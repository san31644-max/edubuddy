<?php
declare(strict_types=1);

require_once __DIR__.'/db.php';

function normalize_referral_code(string $code): string
{
    return strtoupper((string)preg_replace('/\s+/u', ' ', trim($code)));
}

function referral_code_is_valid_format(string $code): bool
{
    return (bool)preg_match('/^(?=.{4,24}$)[A-Z0-9][A-Z0-9 -]*[A-Z0-9]$/D', $code);
}

function active_referral_promoter(string $code): ?array
{
    $code = normalize_referral_code($code);
    if (!referral_code_is_valid_format($code)) return null;
    return query_one(
        "SELECT r.id,r.full_name,r.referral_code,r.registration_limit,(SELECT COUNT(*) FROM users u WHERE u.referral_promoter_id=r.id) registrations FROM referral_promoters r WHERE r.referral_code=? AND r.status='active' LIMIT 1",
        's',
        [$code]
    );
}

function referral_promoter_has_capacity(?array $promoter): bool
{
    return $promoter !== null && (int)$promoter['registrations'] < (int)$promoter['registration_limit'];
}

function premium_base_price_lkr(): float
{
    static $price = null;
    if ($price !== null) return $price;
    $plan = query_one("SELECT price_lkr FROM subscription_plans WHERE id=1 AND status='active'");
    $fallback = defined('PREMIUM_PRICE_LKR') ? (float)PREMIUM_PRICE_LKR : 300.0;
    return $price = max(0.0, (float)($plan['price_lkr'] ?? $fallback));
}

function referral_discount_lkr(): float
{
    return defined('REFERRAL_DISCOUNT_LKR') ? (float)REFERRAL_DISCOUNT_LKR : 20.0;
}

function premium_offer_for_user(int $userId): array
{
    $base = premium_base_price_lkr();
    $row = query_one(
        'SELECT u.referral_promoter_id,u.referral_code_used,r.referral_code '
        .'FROM users u LEFT JOIN referral_promoters r ON r.id=u.referral_promoter_id WHERE u.id=?',
        'i',
        [$userId]
    );
    $promoterId = isset($row['referral_promoter_id']) ? (int)$row['referral_promoter_id'] : null;
    $discount = $promoterId ? min(referral_discount_lkr(), $base) : 0.0;
    return [
        'base' => $base,
        'discount' => $discount,
        'total' => $base - $discount,
        'promoter_id' => $promoterId,
        'code' => (string)($row['referral_code_used'] ?? $row['referral_code'] ?? ''),
    ];
}

function referral_promoter_user(): ?array
{
    return $_SESSION['referral_promoter'] ?? null;
}

function require_referral_promoter(): void
{
    $promoter = referral_promoter_user();
    if (!$promoter) {
        flash('warning', 'Please log in to open your referral portal.');
        redirect('referral/login.php');
    }
    $fresh = query_one(
        "SELECT id,full_name,email,phone,referral_code,registration_limit,status,created_at FROM referral_promoters WHERE id=? AND status='active'",
        'i',
        [(int)$promoter['id']]
    );
    if (!$fresh) {
        unset($_SESSION['referral_promoter']);
        flash('warning', 'This referral account is not active.');
        redirect('referral/login.php');
    }
    $_SESSION['referral_promoter'] = $fresh;
}

function require_referral_promoter_guest(): void
{
    if (referral_promoter_user()) redirect('referral/dashboard.php');
}
