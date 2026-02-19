<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    protected $fillable = [
        'report_by', 'post_id', 'type', 'comment_id', 'community_id', 'pet_id', 'profile_id', 'message_id', 'reason', 'status'
    ];
    
    public function reported_user(){
     return $this->belongsTo(User::class,'report_by');   
    }
    
    public function profile(){
      return $this->belongsTo(User::class,'profile_id');   
    }
    
    public function post(){
      return $this->belongsTo(Post::class,'post_id');   
    }
    
    public function comment(){
      return $this->belongsTo(Comment::class,'comment_id');   
    }
    
    public function community(){
      return $this->belongsTo(Community::class,'community_id');   
    }
    
    public function pet(){
      return $this->belongsTo(Pet::class,'pet_id');   
    }
    
    public function message(){
      return $this->belongsTo(CommunityMessage::class,'message_id');   
    }
}
