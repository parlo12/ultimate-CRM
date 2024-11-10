<?php

    namespace App\Models;

    use App\Library\Traits\HasUid;
    use Illuminate\Database\Eloquent\Model;

    /**
     * @method static where(string $string, string $uid)
     * @method static create(array $array)
     */
    class ChatBox extends Model
    {
        use HasUid;

        protected $fillable = [
            'user_id',
            'from',
            'to',
            'notification',
            'sending_server_id',
            'reply_by_customer',
            'is_starred',
            'recovered'
        ];

        protected $casts = [
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'notification' => 'integer',
        ];

        public function chatBoxMessages()
        {
            return $this->hasMany(ChatBoxMessage::class, 'box_id', 'id');
        }

        public function boxMessages()
        {
            $this->belongsTo(ChatBoxMessage::class, 'box_id', 'id');
        }

    }
