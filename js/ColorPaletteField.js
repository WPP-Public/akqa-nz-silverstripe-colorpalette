// Enhance color palette fields in both PHP-rendered and React (Elemental) CMS forms.
(function ($) {
    const parsePalette = (node) => {
        const raw = node.getAttribute("data-palette");
        if (!raw) {
            return [];
        }
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    };

    const setInputColor = (input, color) => {
        if (!input || !color) {
            return;
        }
        input.value = color;
        input.style.backgroundColor = color;
        input.dispatchEvent(new Event("input", { bubbles: true }));
        input.dispatchEvent(new Event("change", { bubbles: true }));
        $(input).trigger("change");
    };

    const markSelectedSwatch = (holder, color) => {
        if (!holder) {
            return;
        }
        holder.querySelectorAll(".colorpalette__swatch").forEach((swatch) => {
            const selected =
                (swatch.getAttribute("data-color") || "").toLowerCase() ===
                String(color || "").toLowerCase();
            swatch.classList.toggle("is-selected", selected);
            if (swatch.parentElement) {
                swatch.parentElement.classList.toggle("selected", selected);
            }
        });
    };

    // React copies a field's extraClass onto the holder as well as the input, so
    // the hook classes match both. Only the input is ever a real picker.
    const resolveInput = (node) => {
        if (!node || node.nodeType !== 1) {
            return null;
        }
        if (node.tagName === "INPUT") {
            return node;
        }
        return node.querySelector("input.js-color-picker, input.colorpalette__picker-input");
    };

    // Start the search at the parent: the input carries the palette classes too,
    // so closest() from the input would just return the input itself.
    const resolveHolder = (input) => {
        const parent = input.parentElement;
        if (!parent) {
            return null;
        }
        return parent.closest(".colorpalette") || parent.closest(".form__field-holder") || parent;
    };

    const ensureSwatches = (input) => {
        // PHP templates already render swatches; React TextField needs them injected.
        const holder = resolveHolder(input);
        if (!holder || holder.querySelector(".colorpalette__swatch, .colorpalette ul li label")) {
            return holder;
        }

        const colors = parsePalette(input);
        if (!colors.length) {
            return holder;
        }

        const ul = document.createElement("ul");
        ul.className = "colorpalette__swatches";
        colors.forEach((color) => {
            const li = document.createElement("li");
            const button = document.createElement("button");
            button.type = "button";
            button.className = "colorpalette__swatch";
            button.setAttribute("data-color", color);
            button.setAttribute("title", color);
            button.setAttribute("aria-label", color);
            button.style.background = color;
            li.appendChild(button);
            ul.appendChild(li);
        });
        // Insert against the input's own parent; the holder is often an ancestor.
        input.parentElement.insertBefore(ul, input);
        holder.classList.add("colorpalette", "colorpalette--allow-picker");
        return holder;
    };

    const initIrisPicker = (node) => {
        const input = resolveInput(node);
        if (!input || !$.fn.iris || input.dataset.irisReady === "1") {
            return;
        }
        input.dataset.irisReady = "1";
        input.classList.add("js-color-picker");

        const palettes = parsePalette(input);
        const holder = ensureSwatches(input);

        $(input).iris({
            palettes: palettes.length ? palettes : true,
            change: function (event, ui) {
                const color = ui.color.toString();
                input.style.backgroundColor = color;
                markSelectedSwatch(holder, color);
            },
        });

        $(input).on("click.colorpalette", function () {
            $(".js-color-picker").iris("hide");
            $(this).iris("show");
        });

        if (holder && !holder.dataset.swatchBound) {
            holder.dataset.swatchBound = "1";
            holder.addEventListener("click", function (event) {
                const swatch = event.target.closest(".colorpalette__swatch");
                if (!swatch || swatch.disabled) {
                    return;
                }
                event.preventDefault();
                const color = swatch.getAttribute("data-color");
                setInputColor(input, color);
                markSelectedSwatch(holder, color);
                if ($.fn.iris) {
                    $(input).iris("color", color);
                    $(input).iris("hide");
                }
            });
        }

        markSelectedSwatch(holder, input.value);
    };

    const styleReactOptionset = (node) => {
        // React OptionsetField renders titles as text; paint them as swatches.
        const labels = node.querySelectorAll(".form-check-label");
        if (!labels.length) {
            return;
        }

        labels.forEach((label) => {
            label.querySelectorAll("span").forEach((span) => {
                const backgroundStyle = span.innerText;
                if (!backgroundStyle) {
                    return;
                }
                label.setAttribute("style", `background: ${backgroundStyle};`);
                span.innerText = "";
            });
        });
    };

    const enhanceNode = (node) => {
        if (!node || typeof node.querySelectorAll !== "function") {
            return;
        }

        node.querySelectorAll(".colorpalette").forEach((palette) => {
            styleReactOptionset(palette);
        });

        node.querySelectorAll(".js-color-picker, .colorpalette--allow-picker .colorpalette__picker-input").forEach(
            (input) => {
                initIrisPicker(input);
            }
        );

        // React TextField path: class applied via schema extraClass.
        if (node.matches && node.matches(".js-color-picker, .colorpalette__picker-input")) {
            initIrisPicker(node);
        }
    };

    // Hide Iris when clicking outside any picker.
    $(document).on("click.colorpalette", function (e) {
        if (!$(e.target).closest(".js-color-picker, .iris-picker, .iris-picker-inner, .colorpalette__swatch").length) {
            $(".js-color-picker").each(function () {
                if ($(this).data("a8cIris")) {
                    $(this).iris("hide");
                }
            });
        }
    });

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (!mutation.addedNodes || !mutation.addedNodes.length) {
                return;
            }
            mutation.addedNodes.forEach(function (node) {
                enhanceNode(node);
            });
        });
    });

    const start = () => {
        enhanceNode(document.body);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", start);
    } else {
        start();
    }
})(jQuery);
