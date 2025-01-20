// Loading Screen
document.addEventListener('DOMContentLoaded', function() {
    // Wrap everything in a setTimeout to ensure minimum loading time
    setTimeout(() => {
        document.body.classList.add('loading-complete');
    }, 2000); // 2 seconds minimum loading time
});

// Theme toggling and persistence
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    
    // Initialize theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Theme toggle button click handler
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
});

// Project cards mouse movement effect
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.project-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
            card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
        });
    });
});

// Contact Form Submission
async function submitForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';
    formMessage.textContent = '';
    formMessage.className = 'form-message';
    
    try {
        const formData = new FormData(form);
        const response = await fetch('process_form.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        // Show success or error message
        formMessage.textContent = result.message;
        formMessage.className = 'form-message ' + (result.success ? 'success' : 'error');
        
        // Reset form if successful
        if (result.success) {
            form.reset();
            // Clear success message after 3 seconds
            setTimeout(() => {
                formMessage.textContent = '';
                formMessage.className = 'form-message';
            }, 3000);
        }
    } catch (error) {
        formMessage.textContent = 'An error occurred. Please try again.';
        formMessage.className = 'form-message error';
    } finally {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Send Message';
    }
    
    return false;
}

// Add event listener to contact form
document.getElementById('contact-form').addEventListener('submit', submitForm);
