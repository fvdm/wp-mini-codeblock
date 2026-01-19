(function() {
    'use strict';

    // Add scroll hint classes to wrappers
    function attachScrollHints() {
        var wrappers = document.querySelectorAll('.fmc-wrapper');
        for (var i = 0; i < wrappers.length; i++) {
            (function(wrap) {
                var pre = wrap.querySelector('.fmc-pre');
                if (!pre) return;

                var update = function() {
                    wrap.classList.toggle('fmc-wrapper--left', pre.scrollLeft > 0);
                };

                pre.addEventListener('scroll', update, { passive: true });
                update();
            })(wrappers[i]);
        }
    }

    // Initialize copy buttons
    function init() {
        var copyBtns = document.querySelectorAll('.fmc-copy');
        for (var i = 0; i < copyBtns.length; i++) {
            copyBtns[i].addEventListener('click', function() {
                var btn = this;
                var wrapper = btn.closest('.fmc-wrapper');
                var code = wrapper.querySelector('.fmc-code').textContent;
                var trackId = btn.getAttribute('data-id') || 'code-block';

                navigator.clipboard.writeText(code).then(function() {
                    var original = btn.textContent;
                    btn.textContent = 'Copied!';
                    setTimeout(function() { btn.textContent = original; }, 2000);
                });

                // Optional Matomo tracking
                if (typeof Matomo !== 'undefined') {
                    try {
                        Matomo.getAsyncTracker().trackEvent('Interface', 'copy-fmc', trackId);
                    } catch (e) {}
                }
            });
        }

        attachScrollHints();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
