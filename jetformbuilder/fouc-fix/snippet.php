<?php
/**
 * Fix FOUC (Flash of Unstyled Content) in JetFormBuilder Forms
 * 
 * @package     JetFormBuilder
 * @author      Stefan Radu
 * @link        https://www.wordpresstoday.agency/2023/12/11/how-to-fix-fouc-flash-of-unstyled-content-in-jetformbuilders-forms/
 */

// Add CSS to hide form initially
add_action('wp_head', function() {
    ?>
    <style>
        .my_form {
            display: none;
        }
    </style>
    <?php
});

// Add JavaScript to fade in the form
add_action('wp_footer', function() {
    // Check if we should use jQuery or Vanilla JS
    if (wp_script_is('jquery', 'done')) {
        // jQuery version
        ?>
        <script>
            jQuery(document).ready(function($) {
                setTimeout(function() {
                    $('.my_form').fadeIn(1000); // 1 second animation
                }, 1000);
            });
        </script>
        <?php
    } else {
        // Vanilla JavaScript version
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var form = document.querySelector('.my_form');
                    if (form) {
                        var opacity = 0;
                        form.style.display = 'block';
                        var interval = setInterval(function() {
                            if (opacity < 1) {
                                opacity += 0.1;
                                form.style.opacity = opacity;
                            } else {
                                clearInterval(interval);
                            }
                        }, 100);
                    }
                }, 1000);
            });
        </script>
        <?php
    }
});
