// Add event listener for the frontend link in admin area
document.addEventListener('DOMContentLoaded', function() {
    // Find all links to the root URL (frontend)
    const frontendLinks = document.querySelectorAll('a[href="' + yourls_site + '"]');

    // Add the authenticated parameter to all of them
    frontendLinks.forEach(function(link) {
        link.href = yourls_site + '?auth_maintain=1';
    });
});
