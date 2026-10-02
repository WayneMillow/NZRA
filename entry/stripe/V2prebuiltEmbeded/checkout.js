// This is your test publishable API key.
const stripe = Stripe("pk_test_51S6yRZECWSWHof4WfzNeZcOGi70Mgr7t0fPXULXyujD4ivyHe7S61jd0aWYN1iUViSYimrorN6MGWLyjxECKf1r400P6ZhEpOX");

initialize();

// Create a Checkout Session
async function initialize() {
  const fetchClientSecret = async () => {
    const response = await fetch("checkout.php", {
      method: "POST",
    });
    const { clientSecret } = await response.json();
    return clientSecret;
  };

  const checkout = await stripe.initEmbeddedCheckout({
    fetchClientSecret,
  });

  // Mount Checkout
  checkout.mount('#checkout');
}