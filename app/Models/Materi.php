<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';
    protected $primaryKey = 'id_materi';
    protected $guarded = ['id_materi'];

    public function subBab()
    {
        return $this->belongsTo(SubBab::class, 'id_sub_bab', 'id_sub_bab');
    }

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'id_bab', 'id_bab');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'id_quiz', 'id_quiz');
    }

    /**
     * Ekstrak YouTube Video ID dari berbagai format link YouTube:
     * - https://www.youtube.com/watch?v=...
     * - https://youtu.be/...
     * - https://www.youtube.com/shorts/...
     * - https://www.youtube.com/live/...
     * - https://www.youtube.com/embed/...
     */
    public function getYoutubeIdAttribute()
    {
        if (!$this->url_video) {
            return null;
        }
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts|live)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $this->url_video, $match)) {
            return $match[1];
        }
        return null;
    }

    public function getYoutubeEmbedUrlAttribute()
    {
        $id = $this->youtube_id;
        if (!$id) {
            return null;
        }
        return "https://www.youtube.com/embed/{$id}?rel=0&enablejsapi=1";
    }

    public function getYoutubeWatchUrlAttribute()
    {
        $id = $this->youtube_id;
        if (!$id) {
            return $this->url_video;
        }
        return "https://www.youtube.com/watch?v={$id}";
    }
}

