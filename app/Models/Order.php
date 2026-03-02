<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_type',
        'cashier_id',
        'total_price',
        'subtotal',
        'discount_total',
        'tax_amount',
        'grand_total',
        'payment_status',
        'payment_method',
        'void_reason',
        'void_notes',
        'voided_at',
        'voided_by',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * Get the customer that placed the order (nullable, only students)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the cashier that processed the order
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the user who voided this order (nullable)
     */
    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Get all items in this order
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    /**
     * Scope for paid orders only
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for today's orders
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this month's orders
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope for voided orders
     */
    public function scopeVoided($query)
    {
        return $query->whereNotNull('voided_at');
    }

    /**
     * Scope for non-voided orders
     */
    public function scopeNotVoided($query)
    {
        return $query->whereNull('voided_at');
    }

    /**
     * Scope by customer type
     */
    public function scopeOfCustomerType($query, string $type)
    {
        return $query->where('customer_type', $type);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    /**
     * Check if this order has been voided.
     */
    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * Check if customer is a student.
     */
    public function isStudentOrder(): bool
    {
        return $this->customer_type === 'student';
    }

    /**
     * Void this order with reason and audit trail.
     */
    public function void(array $data): void
    {
        $this->update([
            'void_reason' => $data['void_reason'],
            'void_notes' => $data['void_notes'] ?? null,
            'voided_at' => now(),
            'voided_by' => auth()->id(),
            'payment_status' => 'refunded',
        ]);
    }

    /**
     * Get display name for the customer.
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer_name ?? 'Anonim';
    }
}
