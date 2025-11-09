<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $domain
 */
class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'domain'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
