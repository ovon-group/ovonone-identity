<?php

namespace App\Models\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait HasFiles
{
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related')->withTrashed();
    }

    public function addFiles(array $files)
    {
        $this->files()->saveMany(
            collect($files)->map(function ($data) {
                $segments = explode(DIRECTORY_SEPARATOR, $data['path']);
                $segments[0] = 'vault';
                $newPath = implode(DIRECTORY_SEPARATOR, $segments);
                Storage::move($data['path'], $newPath);

                return new File(array_merge($data, [
                    'account_id' => Auth::user()->account_id,
                    'path' => $newPath,
                ]));
            })
        );
    }

    abstract public function getAccountId(): int;
}
