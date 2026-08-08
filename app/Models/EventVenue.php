<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVenue extends Model
{
    protected $fillable = [
        'event_id',
        'sort_order',
        'name',
        'floor',
        'hall_room',
        'description',
        'latitude',
        'longitude',
        'maps_url',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hasMapPin(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function googleMapsLink(): ?string
    {
        if ($this->maps_url) {
            return $this->maps_url;
        }

        if ($this->hasMapPin()) {
            return 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude;
        }

        return null;
    }

    public function directionsUrl(): ?string
    {
        if ($this->hasMapPin()) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
        }

        if ($this->maps_url) {
            return $this->maps_url;
        }

        if ($this->name) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($this->name);
        }

        return null;
    }

    public function embedMapsUrl(): ?string
    {
        if (! $this->hasMapPin()) {
            return null;
        }

        return 'https://maps.google.com/maps?q='.$this->latitude.','.$this->longitude.'&z=15&output=embed';
    }

    public function locationSummary(): string
    {
        $parts = array_filter([
            $this->name,
            $this->floor ? 'Floor '.$this->floor : null,
            $this->hall_room ? 'Hall/Room '.$this->hall_room : null,
        ]);

        return implode(' · ', $parts);
    }
}
