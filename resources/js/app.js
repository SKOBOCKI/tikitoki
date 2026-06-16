document.addEventListener("DOMContentLoaded", function () {
    const feedStack = document.querySelector(".feed-stack");
    const commentToggles = document.querySelectorAll(".comment-toggle");
    const commentCloses = document.querySelectorAll(".comments-close");
    const createDrawer = document.getElementById("create-post");

    const closeComments = () => {
        document.querySelectorAll(".comments-panel.is-open").forEach((panel) => {
            panel.classList.remove("is-open");
            panel.setAttribute("aria-hidden", "true");
            panel.closest(".feed-card")?.classList.remove("has-open-comments");
        });

        commentToggles.forEach((button) => {
            button.classList.remove("is-active");
            button.setAttribute("aria-expanded", "false");
        });

        feedStack?.classList.remove("has-open-comments");
    };

    const updateCreateDrawerFromHash = () => {
        if (!createDrawer) {
            return;
        }

        if (window.location.hash === "#create-post") {
            createDrawer.open = true;
            createDrawer.querySelector("textarea, input, select")?.focus();
        }
    };

    updateCreateDrawerFromHash();
    window.addEventListener("hashchange", updateCreateDrawerFromHash);

    commentToggles.forEach((toggle) => {
        toggle.addEventListener("click", function () {
            const targetId = this.getAttribute("data-comments-target");
            const commentsPanel = document.getElementById(targetId);

            if (commentsPanel) {
                const isOpen = commentsPanel.classList.contains("is-open");

                // Inchide toate comentariile deschise
                document
                    .querySelectorAll(".comments-panel.is-open")
                    .forEach((panel) => {
                        if (panel !== commentsPanel) {
                            panel.classList.remove("is-open");
                            panel.setAttribute("aria-hidden", "true");
                            panel.closest(".feed-card")?.classList.remove("has-open-comments");
                        }
                    });

                commentToggles.forEach((button) => {
                    if (button !== this) {
                        button.classList.remove("is-active");
                        button.setAttribute("aria-expanded", "false");
                    }
                });

                // Toggling
                if (!isOpen) {
                    commentsPanel.classList.add("is-open");
                    commentsPanel.setAttribute("aria-hidden", "false");
                    commentsPanel.closest(".feed-card")?.classList.add("has-open-comments");
                    this.classList.add("is-active");
                    this.setAttribute("aria-expanded", "true");
                    if (feedStack) {
                        feedStack.classList.add("has-open-comments");
                    }
                } else {
                    commentsPanel.classList.remove("is-open");
                    commentsPanel.setAttribute("aria-hidden", "true");
                    commentsPanel.closest(".feed-card")?.classList.remove("has-open-comments");
                    this.classList.remove("is-active");
                    this.setAttribute("aria-expanded", "false");
                    if (feedStack) {
                        feedStack.classList.remove("has-open-comments");
                    }
                }
            }
        });
    });

    commentCloses.forEach((close) => {
        close.addEventListener("click", function () {
            const commentsPanel = this.closest(".comments-panel");

            if (commentsPanel) {
                closeComments();
            }
        });
    });

    // Inchide comentariile cand se face scroll in feed-stack
    feedStack?.addEventListener("scroll", function (event) {
        // Doar daca nu scrollam pe container-ul comentariilor
        if (event.target === feedStack) {
            closeComments();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeComments();
            if (createDrawer?.open) {
                createDrawer.open = false;
            }
        }
    });

    document.querySelectorAll(".create-panel").forEach((form) => {
        const uploadInput = form.querySelector("[data-upload-input]");
        const uploadName = form.querySelector("[data-upload-name]");
        const preview = document.querySelector("[data-upload-preview]");
        const emptyPreview = document.querySelector("[data-upload-empty]");
        const mediaType = form.querySelector('select[name="media_type"]');
        let previewUrl = null;

        uploadInput?.addEventListener("change", () => {
            const file = uploadInput.files?.[0];

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            if (!file) {
                if (preview) {
                    preview.hidden = true;
                    preview.removeAttribute("src");
                }
                if (emptyPreview) {
                    emptyPreview.hidden = false;
                }
                if (uploadName) {
                    uploadName.textContent = "MP4, WebM or OGG up to 100 MB.";
                }
                return;
            }

            if (mediaType) {
                mediaType.value = "video";
            }

            if (uploadName) {
                uploadName.textContent = file.name;
            }

            if (preview) {
                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
                preview.hidden = false;
                preview.load();
            }

            if (emptyPreview) {
                emptyPreview.hidden = true;
            }
        });
    });

    document.querySelectorAll(".feed-card").forEach((card) => {
        const video = card.querySelector("video.feed-media");
        const progress = card.querySelector("[data-progress]");
        const seek = card.querySelector("[data-seek]");
        const mediaError = card.querySelector("[data-media-error]");
        const toggle = card.querySelector(".media-toggle");
        const volumeButton = card.querySelector("[data-volume-button]");
        const volumeSlider = card.querySelector("[data-volume-slider]");

        if (!video) {
            return;
        }

        const setPausedState = () => {
            card.classList.toggle("is-paused", video.paused);
        };

        const hasVideoDuration = () => Number.isFinite(video.duration) && video.duration > 0;

        const setProgressValue = (time) => {
            if (!progress || !hasVideoDuration()) {
                return;
            }

            const percent = Math.min(100, Math.max(0, (time / video.duration) * 100));
            progress.style.width = `${percent}%`;
        };

        const syncSeekBounds = () => {
            if (!seek) {
                return;
            }

            seek.max = hasVideoDuration() ? String(video.duration) : "0";
            seek.disabled = !hasVideoDuration();
        };

        const updateProgress = () => {
            if (!hasVideoDuration()) {
                return;
            }

            setProgressValue(video.currentTime);

            if (seek) {
                seek.value = String(video.currentTime);
            }
        };

        const updateVolume = () => {
            if (!volumeButton) {
                return;
            }

            volumeButton.classList.toggle("is-muted", video.muted || video.volume === 0);
            volumeButton.setAttribute(
                "aria-label",
                video.muted || video.volume === 0 ? "Unmute video" : "Mute video",
            );
        };

        toggle?.addEventListener("click", async () => {
            if (video.paused) {
                await video.play().catch(() => {});
            } else {
                video.pause();
            }

            setPausedState();
        });

        video.addEventListener("click", () => toggle?.click());
        video.addEventListener("play", setPausedState);
        video.addEventListener("pause", setPausedState);
        video.addEventListener("timeupdate", updateProgress);
        video.addEventListener("loadedmetadata", () => {
            card.classList.remove("has-media-error");
            if (mediaError) {
                mediaError.hidden = true;
            }
            syncSeekBounds();
            updateProgress();
        });
        video.addEventListener("error", () => {
            card.classList.add("has-media-error", "is-paused");
            if (mediaError) {
                mediaError.hidden = false;
            }
            if (seek) {
                seek.disabled = true;
            }
        });

        seek?.addEventListener("input", () => {
            if (!hasVideoDuration()) {
                return;
            }

            const nextTime = Math.min(video.duration, Math.max(0, Number(seek.value)));
            video.currentTime = nextTime;
            setProgressValue(nextTime);
        });

        volumeButton?.addEventListener("click", () => {
            video.muted = !video.muted;
            if (!video.muted && video.volume === 0) {
                video.volume = 0.55;
            }
            if (volumeSlider) {
                volumeSlider.value = video.muted ? 0 : video.volume;
            }
            updateVolume();
        });

        volumeSlider?.addEventListener("input", () => {
            video.volume = Number(volumeSlider.value);
            video.muted = video.volume === 0;
            updateVolume();
        });

        setPausedState();
        syncSeekBounds();
        updateVolume();
    });

    if (feedStack && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    const card = entry.target;
                    const video = card.querySelector("video.feed-media");

                    if (!video) {
                        return;
                    }

                    if (entry.isIntersecting && entry.intersectionRatio > 0.65) {
                        card.classList.add("is-visible");
                        video.play().catch(() => {
                            card.classList.add("is-paused");
                        });
                    } else {
                        card.classList.remove("is-visible");
                        video.pause();
                    }
                });
            },
            {
                root: feedStack,
                threshold: [0, 0.35, 0.65, 0.9],
            },
        );

        document.querySelectorAll(".feed-card").forEach((card) => observer.observe(card));
    }

    document.querySelectorAll(".round-action, .watch-action").forEach((button) => {
        button.addEventListener("click", () => {
            button.classList.add("is-pressed");

            if (button.classList.contains("liked")) {
                button.classList.add("is-popping");
            }

            window.setTimeout(() => {
                button.classList.remove("is-pressed", "is-popping");
            }, 340);
        });
    });

    document.querySelectorAll("[data-chat-app]").forEach((chatApp) => {
        const currentUserId = Number(chatApp.dataset.currentUser);
        const messagesWrap = chatApp.querySelector("[data-chat-messages]");
        const sendForm = chatApp.querySelector("[data-chat-send]");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
        let polling = false;

        if (!messagesWrap || !sendForm) {
            return;
        }

        const getLastMessageId = () => {
            const messages = messagesWrap.querySelectorAll("[data-message-id]");
            const last = messages[messages.length - 1];

            return Number(last?.dataset.messageId ?? 0);
        };

        const scrollToLatest = () => {
            messagesWrap.scrollTop = messagesWrap.scrollHeight;
        };

        const appendMessage = (message) => {
            if (messagesWrap.querySelector(`[data-message-id="${message.id}"]`)) {
                return;
            }

            messagesWrap.querySelector("[data-empty-chat]")?.remove();

            const row = document.createElement("div");
            row.className =
                Number(message.user_id) === currentUserId
                    ? "chat-message own"
                    : "chat-message";
            row.dataset.messageId = message.id;

            const avatar = document.createElement("img");
            avatar.src = message.avatar_url;
            avatar.alt = message.user_name;

            const bubble = document.createElement("div");
            const meta = document.createElement("span");
            const body = document.createElement("p");

            meta.textContent = `@${message.username} · ${message.created_at}`;
            body.textContent = message.body;

            bubble.append(meta, body);
            row.append(avatar, bubble);
            messagesWrap.append(row);
        };

        const fetchMessages = async () => {
            if (polling) {
                return;
            }

            polling = true;

            try {
                const url = new URL(messagesWrap.dataset.messagesUrl, window.location.origin);
                url.searchParams.set("after_id", getLastMessageId());

                const response = await fetch(url, {
                    headers: {
                        Accept: "application/json",
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const wasNearBottom =
                    messagesWrap.scrollHeight -
                        messagesWrap.scrollTop -
                        messagesWrap.clientHeight <
                    120;

                data.messages.forEach(appendMessage);

                if (data.messages.length && wasNearBottom) {
                    scrollToLatest();
                }
            } finally {
                polling = false;
            }
        };

        sendForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const input = sendForm.querySelector('input[name="body"]');
            const body = input?.value.trim();

            if (!body || !csrfToken) {
                return;
            }

            const button = sendForm.querySelector("button");
            button.disabled = true;

            try {
                const response = await fetch(sendForm.dataset.sendUrl, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ body }),
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                appendMessage(data.message);
                input.value = "";
                scrollToLatest();
            } finally {
                button.disabled = false;
                input?.focus();
            }
        });

        scrollToLatest();
        window.setInterval(fetchMessages, 2200);
    });

    document.querySelectorAll("[data-share-url]").forEach((button) => {
        button.addEventListener("click", async () => {
            const url = button.dataset.shareUrl;
            const title = button.dataset.shareTitle ?? "TikiToki clip";
            const label = button.querySelector("[data-action-label]");
            const previousText = label?.textContent ?? button.textContent;
            const previousAria = button.getAttribute("aria-label");

            button.classList.add("is-copied");

            try {
                if (navigator.share) {
                    await navigator.share({ title, url });
                    if (label) {
                        label.textContent = "Shared";
                    } else {
                        button.textContent = "Shared";
                    }
                    button.setAttribute("aria-label", "Shared");
                } else {
                    await navigator.clipboard.writeText(url);
                    if (label) {
                        label.textContent = "Copied";
                    } else {
                        button.textContent = "Copied";
                    }
                    button.setAttribute("aria-label", "Link copied");
                }
            } catch {
                if (label) {
                    label.textContent = "Copy failed";
                } else {
                    button.textContent = "Copy failed";
                }
                button.setAttribute("aria-label", "Copy failed");
            }

            window.setTimeout(() => {
                if (label) {
                    label.textContent = previousText;
                } else {
                    button.textContent = previousText;
                }
                button.classList.remove("is-copied");
                if (previousAria) {
                    button.setAttribute("aria-label", previousAria);
                }
            }, 1400);
        });
    });

    document.querySelectorAll("[data-profile-editor]").forEach((editor) => {
        const avatarInput = editor.querySelector("[data-avatar-input]");
        const avatarPreview = editor.querySelector("[data-avatar-preview]");
        const bannerInput = editor.querySelector("[data-banner-input]");
        const bannerPreview = editor.querySelector("[data-banner-preview]");
        const bannerModes = editor.querySelectorAll("[data-banner-mode]");
        const bannerUrlWrap = editor.querySelector("[data-banner-url-wrap]");
        const drawingStudio = editor.querySelector("[data-drawing-studio]");
        const canvas = editor.querySelector("[data-banner-canvas]");
        const drawingInput = editor.querySelector("[data-banner-drawing]");
        const drawColor = editor.querySelector("[data-draw-color]");
        const drawSize = editor.querySelector("[data-draw-size]");
        const clearCanvas = editor.querySelector("[data-clear-canvas]");

        avatarInput?.addEventListener("input", () => {
            if (avatarInput.value && avatarPreview) {
                avatarPreview.src = avatarInput.value;
            }
        });

        bannerInput?.addEventListener("input", () => {
            if (bannerPreview) {
                bannerPreview.style.backgroundImage = bannerInput.value
                    ? `url("${bannerInput.value}")`
                    : "";
            }
        });

        const setBannerMode = () => {
            const selectedMode =
                editor.querySelector("[data-banner-mode]:checked")?.value ??
                "image";

            if (bannerUrlWrap) {
                bannerUrlWrap.hidden = selectedMode !== "image";
            }

            if (drawingStudio) {
                drawingStudio.hidden = selectedMode !== "drawing";
            }
        };

        bannerModes.forEach((mode) => {
            mode.addEventListener("change", setBannerMode);
        });
        setBannerMode();

        if (!canvas || !drawingInput) {
            return;
        }

        const context = canvas.getContext("2d");
        let drawing = false;
        let lastPoint = null;

        const fillCanvas = () => {
            context.fillStyle = "#121212";
            context.fillRect(0, 0, canvas.width, canvas.height);
        };

        const saveCanvas = () => {
            drawingInput.value = canvas.toDataURL("image/png");
            if (bannerPreview) {
                bannerPreview.style.backgroundImage = `url("${drawingInput.value}")`;
            }
        };

        const existingBanner = canvas.dataset.existingBanner;
        if (existingBanner) {
            const image = new Image();
            image.onload = () => {
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                saveCanvas();
            };
            image.src = existingBanner;
        } else {
            fillCanvas();
            saveCanvas();
        }

        const getPoint = (event) => {
            const rect = canvas.getBoundingClientRect();
            const pointer = event.touches?.[0] ?? event;

            return {
                x: ((pointer.clientX - rect.left) / rect.width) * canvas.width,
                y: ((pointer.clientY - rect.top) / rect.height) * canvas.height,
            };
        };

        const drawLine = (point) => {
            context.lineCap = "round";
            context.lineJoin = "round";
            context.strokeStyle = drawColor?.value ?? "#ff2d55";
            context.lineWidth = Number(drawSize?.value ?? 12);

            context.beginPath();
            context.moveTo(lastPoint.x, lastPoint.y);
            context.lineTo(point.x, point.y);
            context.stroke();
        };

        const startDrawing = (event) => {
            drawing = true;
            lastPoint = getPoint(event);
            event.preventDefault();
        };

        const continueDrawing = (event) => {
            if (!drawing) {
                return;
            }

            const point = getPoint(event);
            drawLine(point);
            lastPoint = point;
            saveCanvas();
            event.preventDefault();
        };

        const stopDrawing = () => {
            drawing = false;
            lastPoint = null;
        };

        canvas.addEventListener("mousedown", startDrawing);
        canvas.addEventListener("mousemove", continueDrawing);
        window.addEventListener("mouseup", stopDrawing);
        canvas.addEventListener("touchstart", startDrawing, { passive: false });
        canvas.addEventListener("touchmove", continueDrawing, { passive: false });
        window.addEventListener("touchend", stopDrawing);

        clearCanvas?.addEventListener("click", () => {
            fillCanvas();
            saveCanvas();
        });

        editor.addEventListener("submit", saveCanvas);
    });
});
