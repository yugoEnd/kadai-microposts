<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    public static $user_id = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
     public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
     /**
     * このユーザに関係するモデルの件数をロードする。
     */
    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers','favorites']);
    }
   
   
   
    /**
     * このユーザがフォロー中のユーザ。（Userモデルとの関係を定義）
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    /**
     * このユーザをフォロー中のユーザ。（Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }

    /* $userIdで指定されたユーザをフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function follow($userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            return false;
        } else {
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    /**
     * $userIdで指定されたユーザをアンフォローする。
     * 
     * @param  int $usereId
     * @return bool
     */
    public function unfollow($userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        if ($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 指定された$userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
     * 
     * @param  int $userId
     * @return bool
     */
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
      /**
     * このユーザとフォロー中ユーザの投稿に絞り込む。
     */
    public function feed_microposts()
    {
    // このユーザがフォロー中のユーザのidを取得して配列にする
    $userIds = $this->followings()->pluck('users.id')->toArray();
    // ログインユーザのidをその配列に追加
    $userIds[] = $this->id;
    // それらのユーザが所有する投稿に絞り込む
    return Micropost::whereIn('user_id', $userIds);
    }
     public function feed_favorite_microposts()
    {
    // このユーザがフォロー中のユーザのidを取得して配列にする
    $micropostIds  = $this->favorite()->pluck('microposts.id')->toArray();
    // ログインユーザのidをその配列に追加
    $micropostIds[] = $this->id;
    // それらのユーザが所有する投稿に絞り込む
    return Micropost::whereIn('user_id', $micropostIds);
    }
    
    public function favorite($micropostId)
    {
        $this->favorites()->attach($micropostId);
    }
    
    /**
     * 指定された $micropostId の投稿をお気に入り解除する。
     *
     * @param  int $micropostId
     * @return bool
     */
    public function unfavorite($micropostId)
    {
        $this->favorites()->detach($micropostId);
    }
    

    public function is_favorites($micropostId)
    {
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }


    // このユーザがお気に入り中の投稿一覧。（Userモデルとの関係を定義）
    public function favorites()
    {        
        return $this->belongsToMany(Micropost::class, 'user_favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
}
