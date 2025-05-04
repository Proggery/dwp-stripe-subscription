<?php
namespace DwpStripeSubscription;

use Dotenv\Dotenv;

class Plugin {
    
    public static function init() {
        // Betölteni a környezeti változókat, ha még nem történt meg
        if (!getenv('STRIPE_SECRET_KEY') || !getenv('STRIPE_PUBLISHABLE_KEY')) {
					$dotenv = Dotenv::createImmutable(realpath(__DIR__ . '/../../../../../')); // A gyökérkönyvtár elérési útja
					$dotenv->load();
        }

        // Stripe API kulcsok kinyerése az env fájlból
        $stripeSecretKey = getenv('STRIPE_SECRET_KEY');
        $stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY');

        // Ha nem találjuk a kulcsokat, hibát dobunk
        if (!$stripeSecretKey || !$stripePublishableKey) {
            wp_die('Stripe API kulcsok nem találhatók! Kérjük, állítsd be a megfelelő kulcsokat az env fájlban.');
        }

        // Stripe API kulcs beállítása
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        // Shortcode regisztrálása
        add_shortcode('subscribe_button', [self::class, 'render_stripe_checkout_button']);

        // POST kérés kezelők regisztrálása
        add_action('admin_post_nopriv_create_checkout_session', [CheckoutHandler::class, 'handle_checkout_session']);
        add_action('admin_post_create_checkout_session', [CheckoutHandler::class, 'handle_checkout_session']);
    }

    public static function render_stripe_checkout_button() {
        // Stripe Checkout gomb megjelenítése
				$stripeBasicID = getenv('STRIPE_BASIC_ID');
        $stripeProID = getenv('STRIPE_PRO_ID');

				if (!$stripeBasicID || !$stripeProID) {
					wp_die('Stripe Gomb ID hiányzik.');
				}

        ob_start();
        ?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="create_checkout_session">
            <select name="plan">
                <option value="<?= $stripeBasicID; ?>">Basic csomag</option>
                <option value="<?= $stripeProID; ?>">Pro csomag</option>
            </select>
            <button type="submit">Előfizetek</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
