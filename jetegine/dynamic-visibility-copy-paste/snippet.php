<?php
/**
 * JetEngine Dynamic Visibility Copy/Paste
 * 
 * This snippet adds copy/paste functionality for JetEngine's Dynamic Visibility conditions
 * in Elementor, supporting all element types (widgets, sections, columns, containers).
 * 
 * @package     JetEngine
 * @author      Stefan Radu
 * @link        https://www.wordpresstoday.agency/2024/12/06/how-to-copy-paste-jetengines-dynamic-visibility-conditions-using-elementor-jetengine-and-a-simple-code-snippet/
 */

add_action('elementor/editor/before_enqueue_scripts', function() {
    if (!function_exists('jet_engine') || !class_exists('Jet_Engine_Module_Dynamic_Visibility')) {
        return;
    }

    wp_add_inline_script('elementor-editor', '
        jQuery(function($) {
            var storage = null;
            
            function copySettings(element) {
                var container = element.getContainer?.() || element;
                var model = container?.model || element.model;
                
                if (!model) {
                    console.error("Could not get model from element", element);
                    return;
                }

                var settings = model.get("settings");
                storage = {
                    jedv_enabled: settings.get("jedv_enabled"),
                    jedv_type: settings.get("jedv_type"),
                    jedv_relation: settings.get("jedv_relation"),
                    jedv_conditions: JSON.parse(JSON.stringify(settings.get("jedv_conditions") || []))
                };

                elementor.notifications.showToast({
                    message: "Dynamic Visibility settings copied!",
                    duration: 2000
                });
            }

            async function refreshEditorAndPanel(container) {
                try {
                    container.render();
                    var model = container.model;
                    if (!model.get("settings").get("id")) {
                        model.get("settings").set("id", "e" + elementorCommon.helpers.getUniqueId());
                    }

                    $e.run("panel/global/close");
                    
                    setTimeout(async function() {
                        try {
                            await $e.run("panel/editor/open", {
                                model: model,
                                view: container.view
                            });

                            var currentPageView = elementor.getPanelView().getCurrentPageView();
                            if (currentPageView) {
                                if (currentPageView.collection) {
                                    currentPageView.collection.each(function(control) {
                                        if (control.get("name") === "jedv_conditions") {
                                            control.trigger("ready");
                                        }
                                    });
                                }

                                if (currentPageView.activateSection) {
                                    setTimeout(function() {
                                        currentPageView.activateSection("jedv_section");
                                        
                                        var sectionView = currentPageView.children.find(function(view) {
                                            return view.model.get("name") === "jedv_section";
                                        });
                                        
                                        if (sectionView) {
                                            sectionView.render();
                                            sectionView.triggerMethod("ready");
                                        }

                                        currentPageView.$el.find(".elementor-control").each(function() {
                                            var cid = $(this).data("cid");
                                            if (cid) {
                                                var controlView = currentPageView.children.findByModelCid(cid);
                                                if (controlView) {
                                                    controlView.render();
                                                    controlView.triggerMethod("ready");
                                                }
                                            }
                                        });
                                    }, 50);
                                }
                            }

                            if (window.elementorFrontend?.config?.elements?.data[model.cid]) {
                                elementorFrontend.config.elements.data[model.cid].attributes = 
                                    _.extend({}, elementorFrontend.config.elements.data[model.cid].attributes);
                            }
                        } catch (error) {
                            console.error("Error in delayed panel refresh:", error);
                        }
                    }, 100);

                } catch (error) {
                    console.error("Error refreshing editor:", error);
                }
            }

            function pasteSettings(element) {
                if (!storage) {
                    elementor.notifications.showToast({
                        message: "No Dynamic Visibility settings to paste!",
                        type: "warning"
                    });
                    return;
                }

                try {
                    var container = element.getContainer?.() || element;
                    var model = container?.model || element.model;
                    
                    if (!model) {
                        throw new Error("Could not get model from element");
                    }

                    var conditions = storage.jedv_conditions.map(function(condition) {
                        return Object.assign({}, condition, {
                            _id: elementorCommon.helpers.getUniqueId(),
                            condition_id: elementorCommon.helpers.getUniqueId()
                        });
                    });

                    var settings = {
                        jedv_enabled: storage.jedv_enabled,
                        jedv_type: storage.jedv_type,
                        jedv_relation: storage.jedv_relation,
                        jedv_conditions: conditions
                    };

                    $e.run("document/elements/settings", {
                        container: container,
                        settings: settings
                    });

                    if (window.elementorFrontend?.config?.elements?.data[model.cid]) {
                        window.elementorFrontend.config.elements.data[model.cid].attributes = 
                            _.extend({}, window.elementorFrontend.config.elements.data[model.cid].attributes, {
                                jedv_enabled: settings.jedv_enabled
                            });
                    }

                    setTimeout(function() {
                        var $element = $(".elementor-navigator__element[data-model-cid=\"" + model.cid + "\"]");
                        if (settings.jedv_enabled) {
                            if (!$element.hasClass("jedv-hidden")) {
                                $element
                                    .find("> .elementor-navigator__item > .elementor-navigator__element__title > .elementor-navigator__element__title__text")
                                    .after(\'<div class="elementor-navigator__element__jedv-icon" title="Dynamic Visibility"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" fill="red" width="16" height="16" style="margin: 0 5px"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zm151 118.3C226 97.7 269.5 80 320 80c65.2 0 118.8 29.6 159.9 67.7C518.4 183.5 545 226 558.6 256c-12.6 28-36.6 66.8-70.9 100.9l-53.8-42.2c9.1-17.6 14.2-37.5 14.2-58.7c0-70.7-57.3-128-128-128c-32.2 0-61.7 11.9-84.2 31.5l-46.1-36.1zM394.9 284.2l-81.5-63.9c4.2-8.5 6.6-18.2 6.6-28.3c0-5.5-.7-10.9-2-16c.7 0 1.3 0 2 0c44.2 0 80 35.8 80 80c0 9.9-1.8 19.4-5.1 28.2zm9.4 130.3C378.8 425.4 350.7 432 320 432c-65.2 0-118.8-29.6-159.9-67.7C121.6 328.5 95 286 81.4 256c8.3-18.4 21.5-41.5 39.4-64.8L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9-35.7 46.2-87.7 93-131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5l-41.9-33zM192 256c0 70.7 57.3 128 128 128c13.3 0 26.1-2 38.2-5.8L302 334c-23.5-5.4-43.1-21.2-53.7-42.3l-56.1-44.2c-.2 2.8-.3 5.6-.3 8.5z"/></svg></div>\');
                                
                                $element.addClass("jedv-hidden");
                            }
                        } else {
                            $element
                                .find("> .elementor-navigator__item > .elementor-navigator__element__title > .elementor-navigator__element__title__text")
                                .remove();
                            
                            $element.removeClass("jedv-hidden");
                        }
                    }, 100);

                    refreshEditorAndPanel(container);

                    elementor.notifications.showToast({
                        message: "Dynamic Visibility settings pasted!",
                        duration: 2000
                    });

                } catch (error) {
                    console.error("Error pasting visibility settings:", error);
                    elementor.notifications.showToast({
                        message: "Error pasting visibility settings", 
                        type: "error"
                    });
                }
            }

            function addContextMenuItems() {
                ["widget", "section", "column", "container"].forEach(function(elementType) {
                    elementor.hooks.addFilter("elements/" + elementType + "/contextMenuGroups", 
                        function(groups, element) {
                            groups.push({
                                name: "jedv_actions",
                                actions: [
                                    {
                                        name: "copy_visibility",
                                        title: "Copy Dynamic Visibility",
                                        icon: "eicon-copy",
                                        callback: function() {
                                            copySettings(element);
                                        }
                                    },
                                    {
                                        name: "paste_visibility",
                                        title: "Paste Dynamic Visibility",
                                        icon: "eicon-paste",
                                        callback: function() {
                                            pasteSettings(element);
                                        }
                                    }
                                ]
                            });
                            return groups;
                        }
                    );
                });
            }

            addContextMenuItems();
            
            $(document).on("keydown", function(e) {
                var element = elementor.selection.getElements()[0];
                if (!element) return;

                if ((e.ctrlKey || e.metaKey) && e.shiftKey) {
                    if (e.key.toLowerCase() === "d") {
                        e.preventDefault();
                        copySettings(element);
                    } else if (e.key.toLowerCase() === "v") {
                        e.preventDefault();
                        pasteSettings(element);
                    }
                }
            });
        });
    ');
});
