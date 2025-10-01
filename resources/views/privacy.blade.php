<x-base-layout>
    <x-navbar />

    <section>
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-24 lg:px-6">
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-gray-900 mb-4">{{
                __('frontend.privacy_title') }}</h1>
            <span class="text-gray-500 text-xl">{{ __('frontend.privacy_subtitle') }}</span>
            <hr class="mt-4 mb-4" />

            <span class="text-gray-700 text-base">{{ __('frontend.last_updated') }}: May 20, 2024</span>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">1. {{
                __('frontend.introduction') }}</h2>
            <hr />
            <p class="mt-6">
                {{ __('frontend.privacy_welcome') }} <a href="{{ config('app.url') }}">{{
                    config('app.url') }}</a>, products, and
                services (collectively, "Services"). {{ config('app.name') }} ("us", "we", or "our") is committed to
                protecting your
                privacy and handling your data in an open and transparent manner.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">2. Information
                Collection
            </h2>
            <hr />
            <p class="mt-6">
                When you use our Services, we collect the following information:
            <ul>
                <li>Email Address: To communicate with you about your account and service updates.</li>
                <li>Payment Information: To process payments for our Services.</li>
            </ul>
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">3. Use of Information
            </h2>
            <hr />
            <p class="mt-6">
                The personal information you provide us is used for the following purposes:

                To provide and maintain our Services; To manage your account and subscriptions; To process your
                transactions; To communicate with you, including sending service updates or information about your
                account.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">4. Cookies and Tracking
                Data
            </h2>
            <hr />
            <p class="mt-6">
                We use cookies and similar tracking technologies to track activity on our Services and hold certain
                information. Cookies are files with a small amount of data which may include an anonymous unique
                identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being
                sent.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">5. Data Sharing
            </h2>
            <hr />
            <p class="mt-6">
                We do not share your personal data with third parties, except as necessary to provide our services or
                comply with legal requirements.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">6. Security of Data
            </h2>
            <hr />
            <p class="mt-6">
                We are committed to protecting the security of your personal data and use appropriate technical and
                organizational measures to do so.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">7. Children’s Privacy
            </h2>
            <hr />
            <p class="mt-6">
                Our Services do not address anyone under the age of 18 ("Children"). We do not knowingly collect
                personally identifiable information from children under 18. If you are a parent or guardian and you are
                aware that your Child has provided us with Personal Data, please contact us.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">8. Changes to This
                Privacy Policy
            </h2>
            <hr />
            <p class="mt-6">
                We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new
                Privacy Policy on this page and updating the "effective date" at the top of this Privacy Policy. We will
                also inform you via email and/or a prominent notice on our Service.
            </p>

            <h2 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 mb-2 mt-8">9. Contact Us
            </h2>
            <hr />
            <p class="mt-6">
                If you have any questions about this Privacy Policy, please contact us by email at <a
                    href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>.
            </p>
        </div>
    </section>

    <x-footer />
</x-base-layout>