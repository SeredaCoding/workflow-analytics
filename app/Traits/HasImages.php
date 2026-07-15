<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasImages
{
    public function addImage(string $path, bool $primary = false): void
    {
        $images = $this->images ?? [];

        if ($primary || empty($images)) {
            array_unshift($images, ["path" => $path, "primary" => true]);
        } else {
            $images[] = ["path" => $path, "primary" => false];
        }

        $this->images = $images;
        $this->save();
    }

    public function removeImage(int $index): void
    {
        $images = $this->images ?? [];

        if (!isset($images[$index])) {
            return;
        }

        Storage::disk("local")->delete($images[$index]["path"]);
        unset($images[$index]);
        $this->images = array_values($images);
        $this->save();
    }

    public function primaryImage(): ?array
    {
        $images = $this->images ?? [];
        foreach ($images as $image) {
            if ($image["primary"] ?? false) {
                return $image;
            }
        }
        return $images[0] ?? null;
    }

    public function getImagePath(int $index): ?string
    {
        $images = $this->images ?? [];
        return $images[$index]["path"] ?? null;
    }
}
