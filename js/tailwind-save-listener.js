document.addEventListener("DOMContentLoaded", function () {
    // Select the right toolbar group
    const rightToolbar = document.querySelector("#bricks-toolbar > ul.group-wrapper.right");

    if (rightToolbar) {
        // Create the regenerate CSS button
        const regenerateCssButton = document.createElement('li');
        regenerateCssButton.classList.add('regenerate-tailwind-css');
        regenerateCssButton.setAttribute('data-balloon', 'Regenerate Tailwind CSS');
        regenerateCssButton.setAttribute('data-balloon-pos', 'bottom');
        regenerateCssButton.setAttribute('tabindex', '0');

        // SVG for regenerate button
        const regenerateSvgHtml = `
            <span class="bricks-svg-wrapper" data-name="regenerate-css">
                <svg width="800px" height="800px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><title>file_type_tailwind</title><path d="M9,13.7q1.4-5.6,7-5.6c5.6,0,6.3,4.2,9.1,4.9q2.8.7,4.9-2.1-1.4,5.6-7,5.6c-5.6,0-6.3-4.2-9.1-4.9Q11.1,10.9,9,13.7ZM2,22.1q1.4-5.6,7-5.6c5.6,0,6.3,4.2,9.1,4.9q2.8.7,4.9-2.1-1.4,5.6-7,5.6c-5.6,0-6.3-4.2-9.1-4.9Q4.1,19.3,2,22.1Z" style="fill:#44a8b3"/></svg>
            </span>
        `;
        regenerateCssButton.innerHTML = regenerateSvgHtml;

        // Regenerate CSS click handler
        regenerateCssButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Get the current page ID dynamically from localized data
            const pageId = tailwind_save_obj.page_id; // Localized from PHP

            // Send AJAX request to trigger PHP function
            jQuery.ajax({
                url: tailwind_save_obj.ajax_url, // Using localized variable for ajax_url
                type: 'POST',
                data: {
                    action: 'regenerate_tailwind_css', // The action hook for our PHP handler
                    page_id: pageId, // Pass the page ID to the server
                },
                success: function (response) {
                    console.log('Response from server:', response); // Log server response
                },
                error: function (error) {
                    console.error('Error:', error); // Log if there's an error
                },
            });
        });

        // Add the regenerate button to the toolbar
        rightToolbar.insertBefore(regenerateCssButton, rightToolbar.lastElementChild);
    }
});
