<?php

namespace App\Tools;


use App\Http\Middleware\TrackLoggedUser;
use App\Models\OrganizationUser;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TrackLoggedUserTools
{
    public static function flushCacheUserFromUser(User $user): void
    {
        Cache::tags([TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_TAG])->put(
            TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_KEY_PREFIX . $user->id,
            $user,
            -5
        );
    }

    public static function getCacheKeyFromTagKey(string $tag_key): string|false
    {
        if (preg_match('`rootdb_cache:' . TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_KEY_PREFIX . '([0-9]{1,10})`', $tag_key, $matches) > 0) {

            return 'logged_user_' . $matches[1];
        }

        return false;
    }

    public static function getCacheKeyFromUserId(int $user_id): string
    {
        return TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_KEY_PREFIX . $user_id;
    }

    public static function getCacheUserFromCacheKey(string $cache_key): User|false
    {
        /** @var User|null $loggedUserCache */
        $loggedUserCache = Cache::tags([TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_TAG])->get($cache_key);

        return is_null($loggedUserCache) ? false : $loggedUserCache;
    }

    public static function getCacheUserFromTagKey(string $tag_key): User|false
    {
        if (!mb_strstr($tag_key, 'logged_user_')) {

            return false;
        }

        $cache_key = self::getCacheKeyFromTagKey($tag_key);
        if (!$cache_key) {

            return false;
        }

        /** @var User|null $loggedUserCache */
        $loggedUserCache = Cache::tags([TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_TAG])->get($cache_key);

        return is_null($loggedUserCache) ? false : $loggedUserCache;
    }

    public static function getCacheUserFromUserId(int $user_id): User|false
    {
        /** @var User|null $loggedUserCache */
        $loggedUserCache = Cache::tags([TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_TAG])->get(self::getCacheKeyFromUserId($user_id));

        return is_null($loggedUserCache) ? false : $loggedUserCache;
    }

    public static function getLoggedUserOnReport(Report $report): array
    {
        /** @var User[] $loggedUsersOnReport */
        $loggedUsersOnReport = [];

        foreach (OrganizationUser::select('user_id')->where('organization_id', '=', $report->organization_id)->get()->all() as $organizationUser) {

            /** @var User|null $loggedUserCache */
            $loggedUserCache = self::getCacheUserFromUserId($organizationUser->user_id);

            if ($loggedUserCache !== false) {

                if ($loggedUserCache->currentOrganizationLoggedUser->getReportLiveConfigs()->contains('report_id', '=', $report->id)) {

                    $loggedUsersOnReport[] = $loggedUserCache;
                }
            }
        }

        return $loggedUsersOnReport;
    }

    public static function updateCacheUserFromUser(User $user): void
    {
        Cache::tags([TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_TAG])->put(
            TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_KEY_PREFIX . $user->id, $user,
            TrackLoggedUser::TTL
        );
    }

    public static function updateSessionAndCacheFromUser(User $user): void
    {
        session()->put('currentOrganizationLoggedUser', serialize($user->currentOrganizationLoggedUser));
        self::updateCacheUserFromUser($user);
    }
}
