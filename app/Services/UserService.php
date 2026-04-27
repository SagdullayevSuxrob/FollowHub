<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Foydalanuvchi profiliga kirish huquqini tekshirish
     */
    public function checkAccess($me, $user)
    {
        $me = auth()->user();
        if ($user->Blocked($me)) {
            return response()->json(["message" => "Siz bu hisob tomonidan bloklangansiz."], 403);
        }

        // 2. Men uni bloklaganmanmi?
        if ($user->isBlocked($me)) {
            return response()->json(["message" => "Siz bu foydalanuvchini bloklagansiz, blokdan chiqaring."], 403);
        }

        // 3. Private profil va obuna tekshiruvi
        if ($user->type === 'private' && $me->id !== $user->id && !$me->isFollowing($user)) {
            return response()->json([
                "user" => $user->username,
                "message" => "Bu hisob shaxsiy, obuna bo'lishingiz kerak."
            ], 403);
        }

        return null;
    }

    // show user profile
    public function userProfile($user)
    {
        $me = auth()->user();

        // Bu foydalanuvchi meni bloklagan
        if ($user->Blocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchi tomonidan bloklangansiz.",
                'code' => 403
            ];
        }

        // Men bu foydalanuvchini bloklaganman
        if ($user->isBlocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchini bloklagansiz, uning profilini ko'rish uchun avval blokdan chiqaring!",
                'code' => 403
            ];
        }

        $user->loadCount(['posts', 'followers', 'following', 'friends']);

        // Get user statistics
        $stats = [
            "Posts" => $user->posts_count,
            "Followers" => $user->followers_count,
            "Following" => $user->following_count,
            "Friends" => $user->friends_count
        ];

        // Bu foydalanuvchi akkaunti shaxsiymi?
        if ($user->isPrivate($me)) {
            return [
                'error' => [
                    "User" => $user->only(['username', 'name']),
                    "status" => $me->getFollowStatus($user, $me),
                    "statistics" => $stats,
                    'message' => 'This account is private',
                ],
                'code' => 200,
            ];
        }

        // Get user suggestions

        // 1. Bloklanganlarni yig'amiz
        $blockedIds = $me->blockedUsers()->pluck('blocked_id')
            ->merge($me->blockedBy()->pluck('blocker_id'))
            ->unique();

        $excludeIds = $me->following()->pluck('following_id')
            ->merge($blockedIds)
            ->push($me->id)
            ->push($user->id)
            ->unique();

        // 2. Suggestion (Tavsiyalar) mantiqi
        if ($me->id === $user->id) {
            // O'z profilimda - Global tavsiyalar
            $suggestions = User::whereNotIn('id', $excludeIds)
                ->inRandomOrder()
                ->limit(5)
                ->get(['id', 'username', 'name']);
        } else {
            // Boshqa profilida - Avval uning do'stlari (mutual), keyin global
            $relatedIds = $user->following()->pluck('following_id')
                ->merge($user->followers()->pluck('follower_id'))
                ->unique();

            $suggestions = User::whereIn('id', $relatedIds)
                ->whereNotIn('id', $excludeIds)
                ->inRandomOrder()
                ->limit(5)
                ->get(['id', 'name', 'username']);

            if ($suggestions->count() < 5) {
                $globalSuggestions = User::whereNotIn('id', $excludeIds)
                    ->whereNotIn('id', $suggestions->pluck('id'))
                    ->inRandomOrder()
                    ->limit(5 - $suggestions->count())
                    ->get(['id', 'name', 'username']);

                $suggestions = $suggestions->merge($globalSuggestions);
            }
        }

        return [
            'success' => [
                "User" => $user->only(['username', 'name', 'email']),
                'statistics' => $stats,
                "status" => $me->getFollowStatus($user, $me),
                "suggestions" => $suggestions,
            ],
            'code' => 200
        ];
    }

    // get followers
    public function getFollowers($user)
    {
        $me = auth()->user();

        // Bu foydalanuvchi meni bloklagan
        if ($user->Blocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchi tomonidan bloklangansiz.",
                'code' => 403
            ];
        }

        // Men bu foydalanuvchini bloklaganman. 
        if ($user->isBlocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchini bloklagansiz, uning obunachilarini ko'rish uchun avval blokdan chiqaring",
                'code' => 403
            ];
        }

        // Bu foydalanuvchi profili shaxsiy
        if ($user->isPrivate($me)) {
            return [
                'error' => [
                    "username" => $user->username,
                    "Followers" => $user->followers_count,
                    'message' => 'Bu hisob shaxsiy.'
                ],
                'code' => 403
            ];
        }

        $followers = $user->followers()->paginate(20);

        return [
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'followers' => $followers,
            ],
            'code' => 200
        ];
    }

    // get following
    public function getFollowing($user)
    {
        $me = auth()->user();

        // Bu foydalanuvchi meni bloklagan
        if ($user->Blocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchi tomonidan bloklangansiz.",
                'code' => 403
            ];
        }

        // Men bu foydalanuvchini bloklaganman. 
        if ($user->isBlocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchini bloklagansiz, uning obunachilarini ko'rish uchun avval blokdan chiqaring",
                'code' => 403
            ];
        }

        // Bu foydalanuvchi profili shaxsiy
        if ($user->isPrivate($me)) {
            return [
                'error' => [
                    'username' => $user->username,
                    'following' => $user->following_count,
                    'message' => "Bu hisob shaxsiy"
                ],
                'code' => 403
            ];
        }

        $following = $user->following()->paginate(20);

        return [
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'following' => $following,
            ],
            'code' => 200,
        ];
    }

    // get friends
    public function getFriends($user)
    {
        $me = auth()->user();

        // Bu foydalanuvchi meni bloklagan
        if ($user->Blocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchi tomonidan bloklangansiz.",
                'code' => 403
            ];
        }

        // Men bu foydalanuvchini bloklaganman. 
        if ($user->isBlocked($me)) {
            return [
                "error" => "Siz bu foydalanuvchini bloklagansiz, uning obunachilarini ko'rish uchun avval blokdan chiqaring",
                'code' => 403
            ];
        }

        // Bu foydalanuvchi profili shaxsiy
        if ($user->isPrivate($me)) {
            return [
                'error' => [
                    "username" => $user->username,
                    "Friends" => $user->friends_count,
                    'message' => 'Bu hisob shaxsiy.'
                ],
                'code' => 403
            ];
        }

        $friends = $user->friends()->paginate(20);

        return [
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'friends' => $friends,
            ],
            'code' => 200
        ];
    }
}
