@extends('layouts.app')

@section('title', 'Terms and Conditions')

@section('content')
<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms and Conditions</h2>
        </div>
        <div class="card-body p-4">
            <div class="terms-content">
                <h3 class="mb-4">1. Acceptance of Terms</h3>
                <p>By accessing and using our auto and motorcycle repair services, you agree to be bound by these Terms and Conditions.</p>

                <h3 class="mb-4 mt-5">2. Service Booking and Appointments</h3>
                <ul>
                    <li>All appointments must be booked through our online system</li>
                    <li>We require at least 24 hours notice for appointment cancellations</li>
                    <li>Late arrivals may result in rescheduling of your appointment</li>
                    <li>We reserve the right to modify or cancel appointments due to unforeseen circumstances</li>
                </ul>

                <h3 class="mb-4 mt-5">3. Vehicle Services</h3>
                <ul>
                    <li>We provide services for both cars and motorcycles</li>
                    <li>All services are performed by certified technicians</li>
                    <li>We use quality parts and materials for all repairs</li>
                    <li>Additional repairs may be required once the vehicle is inspected</li>
                </ul>

                <h3 class="mb-4 mt-5">4. Payment Terms</h3>
                <ul>
                    <li>Payment is due upon completion of services</li>
                    <li>We accept various payment methods including cash, credit cards, and electronic transfers</li>
                    <li>Prices are subject to change without notice</li>
                    <li>Additional charges may apply for parts and materials</li>
                </ul>

                <h3 class="mb-4 mt-5">5. Warranty</h3>
                <ul>
                    <li>We provide a 90-day warranty on parts and labor</li>
                    <li>Warranty does not cover normal wear and tear</li>
                    <li>Warranty is void if the vehicle is serviced elsewhere</li>
                </ul>

                <h3 class="mb-4 mt-5">6. Privacy Policy</h3>
                <p>We collect and process your personal information in accordance with our Privacy Policy. This includes:</p>
                <ul>
                    <li>Contact information (name, email, phone number)</li>
                    <li>Vehicle information</li>
                    <li>Service history</li>
                    <li>Payment information</li>
                </ul>

                <h3 class="mb-4 mt-5">7. Limitation of Liability</h3>
                <p>We are not liable for any indirect, incidental, or consequential damages arising from our services.</p>

                <h3 class="mb-4 mt-5">8. Changes to Terms</h3>
                <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting.</p>

                <div class="text-center mt-5">
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Registration
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.terms-content {
    max-width: 800px;
    margin: 0 auto;
}

.terms-content h3 {
    color: var(--primary-color);
    font-weight: 600;
}

.terms-content ul {
    list-style-type: none;
    padding-left: 0;
}

.terms-content ul li {
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.terms-content ul li:before {
    content: "•";
    color: var(--primary-color);
    position: absolute;
    left: 0;
}

.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
}
</style>
@endsection 