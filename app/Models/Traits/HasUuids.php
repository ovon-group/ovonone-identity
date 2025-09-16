<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\SoftDeletingScope;

trait HasUuids
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return [$this->getRouteKeyName()];
    }

    public static function setMissingUuids(): void
    {
        $keyName = (new static)->getRouteKeyName();

        static::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->whereNull($keyName)->each(function (self $model) {
                $model->setUniqueIds();
                $model->save();
            });
    }
}
