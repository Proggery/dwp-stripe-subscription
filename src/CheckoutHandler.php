<?php
namespace DwpStripeSubscription;

use Dotenv\Dotenv;

class CheckoutHandler {

    public static function handle_checkout_session() {
        // Betöltjük a környezeti változókat
        $dotenv = Dotenv::createImmutable(realpath(__DIR__ . '/../../../../../'));
        $dotenv->load();

        // Stripe Secret Key kinyerése az env fájlból
        $stripe_secret_key = getenv('STRIPE_SECRET_KEY');

        // Ha nincs beállítva a Stripe Secret Key, hibát dobunk
        if (!$stripe_secret_key) {
            wp_die('A Stripe titkos kulcsa nem található! Kérjük, állítsd be az .env fájlban.');
        }

        // A Stripe API kulcs beállítása
        \Stripe\Stripe::setApiKey($stripe_secret_key);

        // Ellenőrizzük, hogy a 'plan' paraméter benne van-e a POST kérésben
        if ( ! isset( $_POST['plan'] ) ) {
            wp_die( 'Hiányzó csomag!' );
        }

        // A csomag adatainak kinyerése
        $plan = sanitize_text_field( $_POST['plan'] );

        // Stripe Checkout Session létrehozása
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $plan, // Árak a Stripe-on
                'quantity' => 1,
            ]],
            'success_url' => home_url('/sikeres-elofizetes'),
            'cancel_url' => home_url('/megszakitva'),
        ]);

        // Átirányítás a Stripe Checkout-ra
        wp_redirect($session->url);
        exit;
    }
}
