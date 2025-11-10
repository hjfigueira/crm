<?php

namespace App\Models;

use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Allow mass assignment for entity fields.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'email',
        'phone',
        'mobile',
        'address_line1',
        'address_line2',
        'address_number',
        'address_district',
        'address_city',
        'address_state',
        'address_postal_code',
        'address_country',
        'company_legal_name',
        'company_trade_name',
        'company_cnpj',
        'company_state_registration',
        'company_municipal_registration',
        'company_website',
        'person_first_name',
        'person_last_name',
        'person_cpf',
        'person_rg',
        'person_birth_date',
        'person_gender',
        'notes',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'person_birth_date' => 'date',
        ];
    }

    /**
     * The tenant that owns this entity.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
