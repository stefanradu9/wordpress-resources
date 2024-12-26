<?php
/**
 * Custom CSS Indicator for Elementor Navigator
 * 
 * This snippet adds a visual indicator (blue dot) in the Elementor navigator panel
 * for elements that have custom CSS applied.
 * 
 * @package     Elementor
 * @author      Stefan Radu
 * @link        https://www.wordpresstoday.agency/2024/12/22/add-custom-css-indicator-to-elementor-navigator-elements/
 */

add_action('elementor/editor/before_enqueue_scripts', function() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'elementor') {
        return;
    }

    wp_add_inline_script('elementor-editor', '
        jQuery(function($) {
            function checkCustomCSS(element) {
                try {
                    var container = element.getContainer?.() || element;
                    var model = container?.model || element.model;
                    
                    if (!model) {
                        return false;
                    }

                    var settings = model.get("settings");
                    return settings.get("custom_css") && settings.get("custom_css").trim() !== "";
                } catch (error) {
                    return false;
                }
            }

            function updateNavigatorIcon(element, hasCustomCSS) {
                try {
                    var model = element.getContainer?.()?.model || element.model;
                    if (!model) return;

                    setTimeout(function() {
                        var $element = $(".elementor-navigator__element[data-model-cid=\"" + model.cid + "\"]");
                        var $icon = $element.find("> .elementor-navigator__item > .elementor-navigator__element__title > .elementor-navigator__element__css-icon");
                        
                        if (hasCustomCSS) {
                            if (!$icon.length) {
                                $element
                                    .find("> .elementor-navigator__item > .elementor-navigator__element__title > .elementor-navigator__element__title__text")
                                    .after(\'<div class="elementor-navigator__element__css-icon" title="Has Custom CSS"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#0073aa" width="8" height="8" style="margin: 0 5px"><circle cx="256" cy="256" r="256"/></svg></div>\');
                            }
                        } else {
                            $icon.remove();
                        }
                    }, 100);
                } catch (error) {
                    console.debug("Error updating icon:", error);
                }
            }

            function findAllElements(view) {
                var elements = [view];
                if (view.children) {
                    view.children.each(function(childView) {
                        elements = elements.concat(findAllElements(childView));
                    });
                }
                return elements;
            }

            function checkFromFrontendConfig(element) {
                try {
                    var model = element.getContainer?.()?.model || element.model;
                    if (!model?.cid) return false;
                    
                    return window.elementorFrontend?.config?.elements?.data[model.cid]?.attributes?.custom_css?.trim();
                } catch (error) {
                    return false;
                }
            }

            // Add styles
            var style = document.createElement("style");
            style.textContent = `
                .elementor-navigator__element__css-icon {
                    display: inline-flex;
                    align-items: center;
                    margin-left: 5px;
                    opacity: 0.9;
                }
                .elementor-navigator__element__css-icon:hover {
                    opacity: 1;
                    transform: scale(1.2);
                    transition: all 0.2s ease;
                }
                .elementor-navigator__element__css-icon svg {
                    filter: drop-shadow(0 0 1px rgba(0,0,0,0.2));
                }
            `;
            document.head.appendChild(style);

            // Original events for click functionality
            elementor.channels.editor.on("editor:element:style:update", function(view) {
                if (!view) return;
                var hasCustomCSS = checkCustomCSS(view);
                updateNavigatorIcon(view, hasCustomCSS);
            });

            elementor.channels.editor.on("change", function() {
                var selectedElement = elementor.selection.getElements()[0];
                if (selectedElement) {
                    var hasCustomCSS = checkCustomCSS(selectedElement);
                    updateNavigatorIcon(selectedElement, hasCustomCSS);
                }
            });

            elementor.hooks.addAction("panel/open_editor/widget", function(panel, model, view) {
                setTimeout(function() {
                    var hasCustomCSS = checkCustomCSS(view);
                    updateNavigatorIcon(view, hasCustomCSS);
                }, 100);
            });

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === "childList" && window.elementorFrontend?.config?.elements?.data) {
                        var mainView = elementor.getPreviewView();
                        if (mainView) {
                            var elements = findAllElements(mainView);
                            elements.forEach((element) => {
                                const hasCSS = checkCustomCSS(element) || checkFromFrontendConfig(element);
                                if (hasCSS) {
                                    updateNavigatorIcon(element, true);
                                }
                            });
                        }
                    }
                });
            });

            function startObserving() {
                const navigator = document.querySelector("#elementor-navigator");
                if (navigator) {
                    observer.observe(navigator, {
                        childList: true,
                        subtree: true
                    });
                } else {
                    setTimeout(startObserving, 500);
                }
            }

            elementor.on("preview:loaded", () => {
                startObserving();
            });
        });
    ');
});