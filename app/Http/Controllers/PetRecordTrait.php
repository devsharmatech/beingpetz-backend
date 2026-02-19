<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait PetRecordTrait
{
   
   protected function generateBgColor(): string
{
    
    $r = mt_rand(220, 255);
    $g = mt_rand(220, 255);
    $b = mt_rand(220, 255);

    return sprintf('#%02X%02X%02X', $r, $g, $b);
}

   
    protected function handleImageUpload($file, string $prefix): string
    {
        $manager = new ImageManager(new Driver());
        $filename = uniqid($prefix) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/pet_records/' . $filename;
        $manager->read($file)
            ->save(public_path($path), 100);
        return $path;
    }
}
