<x-base-layout>
    <x-navbar />

    <section>
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-24 lg:px-6">
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-gray-900 mb-4">{{
                __('frontend.terms_title') }}</h1>
            <span class="text-gray-500 text-xl">{{ __('frontend.terms_subtitle') }}</span>
            <hr class="mt-4 mb-4" />

            <span class="text-gray-700 text-base">{{ __('frontend.last_updated') }}: May 20, 2024</span>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">1. {{
                __('frontend.introduction') }}</h2>
            <hr />
            <p class="mt-6">
                {{ __('frontend.welcome_message') }} <a href="{{ config('app.url') }}">{{
                    config('app.url') }}</a>
                (hereinafter referred to as "Service") operated by {{ config('app.name') }} ("us", "we", or "our").
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">2. Agreement to Terms
            </h2>
            <hr />
            <p class="mt-6">
                By using our Service, you agree to be bound by these Terms & Conditions. If you disagree with any part
                of the terms, then you do not have permission to access the Service.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">3. Subscriptions
            </h2>
            <hr />
            <p class="mt-6">
                Our Service is billed on a subscription basis ("Subscription(s)"). You will be billed in advance on a
                recurring and periodic basis ("Billing Cycle"). At the end of each Billing Cycle, your Subscription will
                automatically renew under the exact same conditions unless you cancel it or Safeye cancels it. You may
                cancel your Subscription renewal either through your online account management page or by contacting
                {{ config('app.name') }} customer support team at <a href="mailto:{{ config('app.support_email') }}">{{
                    config('app.support_email') }}</a>.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">4. Refunds
            </h2>
            <hr />
            <p class="mt-6">
                We offer a full refund for Subscriptions within 7 days following the date of your Subscription purchase.
                After the 7-day period, you will no longer be eligible to receive a refund.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">5. Collection and Use of
                Your Personal Information
            </h2>
            <hr />
            <p class="mt-6">
                For a complete description of how we use and protect your personal data, please see our Privacy Policy
                at <a href="https://safeye.co/privacy">https://safeye.co/privacy</a>. By using our Service, you agree to
                the collection and use of information in accordance with our Privacy Policy.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">6. Governing Law
            </h2>
            <hr />
            <p class="mt-6">
                These Terms shall be governed and construed in accordance with the laws of Portugal, without regard to
                its conflict of law provisions.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">7. Changes to Terms
            </h2>
            <hr />
            <p class="mt-6">
                We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a
                revision is material, we will provide at least 30 days' notice prior to any new terms taking effect.
                What constitutes a material change will be determined at our sole discretion.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">8. Contact Us
            </h2>
            <hr />
            <p class="mt-6">
                {{ __('frontend.contact_support') }} <a href="mailto:{{ config('app.support_email') }}">{{
                    config('app.support_email') }}</a>.
            </p>
            <p class="mt-4">
                By continuing to access or use our Service after those revisions become effective, you agree to be bound
                by the revised terms. If you do not agree to the new terms, you are no longer authorized to use the
                Service.
            </p>
        </div>
    </section>

    <x-footer />
</x-base-layout>