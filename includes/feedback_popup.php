<!-- Feedback Trigger Button (left side) -->
<button id="feedbackTrigger"
    class="fixed left-0 top-1/2 -translate-y-1/2 z-50 bg-gold-500 hover:bg-gold-400 text-navy-900 py-3 px-1.5 rounded-r-lg shadow-lg cursor-pointer hidden transition-all duration-300"
    onclick="toggleFeedback()">
    <span class="block [writing-mode:vertical-rl] text-[10px] uppercase tracking-widest font-bold">Feedback</span>
</button>

<!-- Feedback Popup -->
<div id="feedbackPopup"
    class="fixed inset-x-0 bottom-0 z-50 transform translate-y-full transition-transform duration-500 ease-out"
    style="display: none;">
    <div class="max-w-md mx-auto mb-6 mx-4 sm:mx-auto">
        <div class="bg-navy-800 border-2 border-gold-500 rounded-lg shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-gold-500 to-gold-400 p-4 flex justify-between items-center">
                <h3 class="text-navy-900 font-serif text-xl font-bold">Share Your Feedback</h3>
                <button onclick="closeFeedbackPopup()" class="text-navy-900 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="feedbackForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Your Name *</label>
                    <input type="text" name="name" required
                        class="w-full bg-navy-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-gold-500"
                        placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email Address *</label>
                    <input type="email" name="email" required
                        class="w-full bg-navy-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-gold-500"
                        placeholder="john@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Your Feedback *</label>
                    <textarea name="message" required rows="4"
                        class="w-full bg-navy-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-gold-500 resize-none"
                        placeholder="Share your thoughts, suggestions, or concerns..."></textarea>
                </div>

                <div id="feedbackMessage" class="hidden text-sm"></div>

                <button type="submit"
                    class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-3 rounded transition-colors cursor-pointer">
                    Submit Feedback
                </button>

                <p class="text-xs text-slate-500 text-center">
                    Your feedback helps us improve our services
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        let hasShown = false;

        function getCookie(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value, hours) {
            let date = new Date();
            date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
            document.cookie = name + "=" + encodeURIComponent(value) + "; expires=" + date.toUTCString() + "; path=/";
        }

        function showFeedbackPopup() {
            if (hasShown) return;
            hasShown = true;

            // Set cookie for 1 hour
            setCookie('feedback_popup_shown', '1', 1);

            const popup = document.getElementById('feedbackPopup');
            popup.style.display = 'block';
            requestAnimationFrame(() => {
                popup.classList.remove('translate-y-full');
            });
        }

        window.closeFeedbackPopup = function () {
            const popup = document.getElementById('feedbackPopup');
            popup.classList.add('translate-y-full');
            setTimeout(() => {
                popup.style.display = 'none';
                document.getElementById('feedbackTrigger').classList.remove('hidden');
            }, 500);
        };

        window.toggleFeedback = function () {
            const popup = document.getElementById('feedbackPopup');
            if (popup.style.display === 'none' || popup.classList.contains('translate-y-full')) {
                popup.style.display = 'block';
                requestAnimationFrame(() => {
                    popup.classList.remove('translate-y-full');
                });
            } else {
                closeFeedbackPopup();
            }
        };

        function handleScroll() {
            const scrollPercent = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight;
            if (scrollPercent >= 0.8) {
                window.removeEventListener('scroll', handleScroll);
                showFeedbackPopup();
            }
        }

        // Initialize feedback form state
        if (getCookie('feedback_popup_shown')) {
            // Already shown, display floating trigger immediately
            document.getElementById('feedbackTrigger').classList.remove('hidden');
        } else {
            // Listen for scroll
            window.addEventListener('scroll', handleScroll);
        }


        document.getElementById('feedbackForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const messageDiv = document.getElementById('feedbackMessage');

            try {
                const response = await fetch('feedback_submit.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                messageDiv.className = result.success
                    ? 'text-green-400 text-sm'
                    : 'text-red-400 text-sm';
                messageDiv.textContent = result.message;
                messageDiv.classList.remove('hidden');

                if (result.success) {
                    e.target.reset();
                    setTimeout(() => {
                        closeFeedbackPopup();
                    }, 2000);
                }
            } catch (error) {
                messageDiv.className = 'text-red-400 text-sm';
                messageDiv.textContent = 'Error submitting feedback. Please try again.';
                messageDiv.classList.remove('hidden');
            }
        });
    })();
</script>
