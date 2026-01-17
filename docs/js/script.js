document.addEventListener('DOMContentLoaded', () => {
    // Helper to create copy buttons
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            let content = '';

            if (targetId) {
                // Copy from specific element
                content = document.getElementById(targetId).textContent;
            } else {
                // Copy from sibling pre/code. 
                // Since button is INSIDE pre now, we need to get pre content minus button text
                const pre = btn.closest('pre');
                if (pre) {
                    const codeBlock = pre.querySelector('code');
                    content = codeBlock ? codeBlock.textContent : pre.textContent.replace('Copy', '').trim();
                }
            }

            if (content) {
                navigator.clipboard.writeText(content).then(() => {
                    const originalHtml = btn.innerHTML;

                    // Add success styles
                    btn.classList.add('copied');
                    btn.innerHTML = '<i class="fas fa-check"></i> COPIED';

                    // Revert after 2s
                    setTimeout(() => {
                        btn.classList.remove('copied');
                        btn.innerHTML = originalHtml;
                    }, 2000);
                });
            }
        });
    });
});
