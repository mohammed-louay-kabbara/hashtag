<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
     protected $fillable = [
        'name', 'email', 'phone', 'datebirthday', 'picture',
        'description', 'role', 'password',
    ];


    public function nubdha(){
      return $this->hasMany(nubdha::class);
    }   
    
    public function nubdha_view(){
      return $this->hasMany(nubdha_view::class);
    }   

    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'followed_id');
    }

    // Users that follow me (هم → يتابعوني)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'user_id');
    }

        protected $appends = ['is_followed'];

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'followed_id');
    }

    // ✅ Accessor ذكي
    protected function isFollowed(): Attribute
    {
        return Attribute::make(
            get: function () {
                // إذا كانت الخاصية موجودة مسبقاً (من المجمّع السريع)، لا تعيد الاستعلام
                if (isset($this->attributes['is_followed'])) {
                    return $this->attributes['is_followed'];
                }

                $authId = Auth::id();
                if (!$authId) return false;

                // استعلام مباشر فقط عند المستخدم الفردي
                return DB::table('followers')
                    ->where('follower_id', $authId)
                    ->where('followed_id', $this->id)
                    ->exists();
            }
        );
    }







    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    // Rest omitted for brevity
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

}
