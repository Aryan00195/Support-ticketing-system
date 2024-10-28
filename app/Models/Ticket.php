<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

    public $table = 'tickets';

    protected $fillable = [
        'title', 'content', 'Attachment', 'user_id', 'status_id', 'category_id', 'subcategory_id'
    ];

    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'assign_agent_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}
