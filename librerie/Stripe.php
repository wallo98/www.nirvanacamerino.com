<?php


// 2. Configurazione iniziale
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('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');



class StripePaymentHandler {
    private $stripe;
    
    public function __construct() {
        $this->stripe = new \Stripe\StripeClient('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');
    }
    
    // Creare un pagamento
    public function createPayment($amount, $currency = 'eur', $description = '') {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Stripe usa i centesimi
                'currency' => $currency,
                'description' => $description,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            
            return $paymentIntent;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Errore nel pagamento: " . $e->getMessage());
        }
    }
    
    // Verificare un pagamento
    public function verifyPayment($paymentIntentId) {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->status === 'succeeded';
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Errore nella verifica: " . $e->getMessage());
        }
    }
}

