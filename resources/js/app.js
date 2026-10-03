import "./bootstrap";
import Alpine from "alpinejs";

// Galerie : filtre par catégorie (synchronisé avec ?categorie=slug)
Alpine.data("gallery", (slugs = []) => ({
    active: "all",

    init() {
        const wanted = new URLSearchParams(window.location.search).get(
            "categorie",
        );
        if (wanted && slugs.includes(wanted)) {
            this.active = wanted;
        }
    },

    set(slug) {
        this.active = slug;
        const url = new URL(window.location.href);
        slug === "all"
            ? url.searchParams.delete("categorie")
            : url.searchParams.set("categorie", slug);
        window.history.replaceState({}, "", url);
    },

    show(slug) {
        return this.active === "all" || this.active === slug;
    },
}));

// Carte média : légende dépliable + lecteur vidéo chargé au clic
Alpine.data("mediaCard", () => ({
    open: false,
    playing: false,
}));

window.Alpine = Alpine;
Alpine.start();
