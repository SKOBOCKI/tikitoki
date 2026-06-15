export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.jsx",
    ],
    theme: {
        extend: {
            colors: {
                tikitoki: {
                    bg: "#0A0A0A",
                    raised: "#121212",
                    surface: "#181818",
                    soft: "rgb(255 255 255 / 0.08)",
                    line: "rgb(255 255 255 / 0.1)",
                    text: "#FFFFFF",
                    muted: "#B7B7B7",
                    dim: "#8F8F8F",
                    accent: "#FF2D55",
                },
            },
            fontFamily: {
                sans: [
                    "Inter",
                    "ui-sans-serif",
                    "system-ui",
                    "-apple-system",
                    "BlinkMacSystemFont",
                    "Segoe UI",
                    "sans-serif",
                ],
            },
            spacing: {
                nav: "4.5rem",
                topbar: "4rem",
            },
            borderRadius: {
                ui: "0.5rem",
                pill: "999px",
            },
            fontSize: {
                label: "0.75rem",
                body: "0.9375rem",
                title: "1.125rem",
                display: "1.75rem",
            },
        },
    },
};
