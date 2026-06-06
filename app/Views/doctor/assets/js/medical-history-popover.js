/**
 * Shared patient history popover — medical records + visit chronology.
 * Used by calendar.js (actions column) and patient profile (History button).
 */
(function (global) {
    "use strict";

    var HISTORY_TITLES = [
        "Chief Complaint", "Plan", "History of Present Illness",
        "Allergies", "Medications", "Systemic", "Ocular",
        "Surgeries", "Family", "Diagnosis", "Treatment",
    ];

    function escapeHtml(text) {
        if (text == null) return "";
        var div = document.createElement("div");
        div.textContent = String(text);
        return div.innerHTML;
    }

    function fmtDate(d) {
        if (!d) return "N/A";
        try {
            return new Date(d).toLocaleDateString("en-US", {
                month: "short", day: "numeric", year: "numeric",
            });
        } catch (_) {
            return String(d);
        }
    }

    function fmtTime(t) {
        if (!t) return "";
        try {
            var parts = String(t).split(":");
            var h = parseInt(parts[0], 10);
            var m = parts[1] || "00";
            var ampm = h >= 12 ? "PM" : "AM";
            h = h % 12 || 12;
            return h + ":" + m + " " + ampm;
        } catch (_) {
            return String(t);
        }
    }

    function statusBadgeClass(status) {
        var s = (status || "").toLowerCase().replace(/\s+/g, "");
        if (s === "completed") return "status-completed";
        if (s === "booked" || s === "checkedin" || s === "inprogress") return "status-booked";
        if (s === "cancelled") return "status-cancelled";
        return "status-noshow";
    }

    function isImageAttachment(att) {
        if (att.mime_type && att.mime_type.indexOf("image/") === 0) return true;
        var name = att.original_filename || att.filename || "";
        var ext = name.split(".").pop().toLowerCase();
        return ["jpg", "jpeg", "png", "gif", "webp", "bmp"].indexOf(ext) >= 0;
    }

    function elevateImageModalAbovePopover() {
        function apply() {
            var modal = document.getElementById("imageModal");
            if (!modal) return;
            modal.style.zIndex = "10050";
            var backdrops = document.querySelectorAll(".modal-backdrop");
            if (backdrops.length) {
                backdrops[backdrops.length - 1].style.zIndex = "10040";
            }
        }
        setTimeout(apply, 0);
        setTimeout(apply, 60);
        setTimeout(apply, 150);
    }

    function openAttachment(att, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var ext = ((att.original_filename || att.filename || "").split(".").pop() || "").toLowerCase();
        if (typeof global.viewPatientAttachment === "function") {
            global.viewPatientAttachment(att.id, att.file_path || "", ext, true);
            elevateImageModalAbovePopover();
        } else if (typeof global.showImageModal === "function") {
            global.showImageModal("/api/attachments/view/" + att.id, att.id, true);
            elevateImageModalAbovePopover();
        } else {
            global.open("/api/attachments/view/" + att.id, "_blank");
        }
    }

    function highlightHistoryNotes(notesText) {
        HISTORY_TITLES.forEach(function (title) {
            var pattern = new RegExp(
                "\\b(" + title.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "):\\s*",
                "gi"
            );
            notesText = notesText.replace(
                pattern,
                '<strong style="color: dodgerblue;">$1:</strong> '
            );
        });
        return notesText;
    }

    function buildHistoryNotes(entry) {
        if (entry.notes && String(entry.notes).trim() !== "") {
            return highlightHistoryNotes(escapeHtml(String(entry.notes).replace(/\r\n|\r|\n/g, " ")));
        }
        if (entry.entry_type === "new_format") return "";
        var parts = [];
        if (entry.allergies) parts.push("Allergies: " + escapeHtml(entry.allergies));
        if (entry.medications) parts.push("Medications: " + escapeHtml(entry.medications));
        if (entry.systemic_history) parts.push("Systemic: " + escapeHtml(entry.systemic_history));
        if (entry.ocular_history) parts.push("Ocular: " + escapeHtml(entry.ocular_history));
        if (entry.prior_surgeries) parts.push("Surgeries: " + escapeHtml(entry.prior_surgeries));
        if (entry.family_history) parts.push("Family: " + escapeHtml(entry.family_history));
        return parts.length ? parts.join(" | ") : "";
    }

    function renderMedicalHistorySection(history) {
        if (!history || !history.length) {
            return '<p class="mhp-empty"><i class="bi bi-inbox me-1"></i>No medical history entries</p>';
        }
        var html = '<div class="medical-history-list">';
        history.forEach(function (entry, index) {
            var date = entry.diagnosis_date || entry.created_at;
            var doctorName = entry.doctor_name || "Unknown";
            var notesText = buildHistoryNotes(entry);
            html += '<div class="medical-history-item' + (index === 0 ? " active" : "") + '">';
            html += '<div class="medical-history-item-header">';
            html += "<h4>" + escapeHtml(entry.condition_name || "Medical Record #" + entry.id) + "</h4>";
            html += '<small><i class="bi bi-calendar me-1"></i>' + escapeHtml(fmtDate(date));
            if (doctorName !== "Unknown") html += " • by " + escapeHtml(doctorName);
            html += "</small></div>";
            if (notesText) html += '<div class="medical-history-item-content">' + notesText + "</div>";
            html += "</div>";
        });
        html += "</div>";
        return html;
    }

    function renderGlassesMini(glasses) {
        if (!glasses || !glasses.length) return "";
        var g = glasses[0];
        var od = "OD " + (g.distance_sphere_r || "—") + "/" + (g.distance_cylinder_r || "—") + "×" + (g.distance_axis_r || "—");
        var os = "OS " + (g.distance_sphere_l || "—") + "/" + (g.distance_cylinder_l || "—") + "×" + (g.distance_axis_l || "—");
        var line = od + " · " + os;
        if (glasses.length > 1) line += " (+" + (glasses.length - 1) + ")";
        return '<div class="mhp-mini-row"><span class="mhp-mini-label"><i class="bi bi-eyeglasses"></i></span>'
            + '<span class="mhp-mini-glasses">' + escapeHtml(line) + "</span></div>";
    }

    function renderMedsMini(meds) {
        if (!meds || !meds.length) return "";
        var html = '<div class="mhp-mini-row"><span class="mhp-mini-label"><i class="bi bi-capsule"></i></span>';
        meds.slice(0, 4).forEach(function (m) {
            html += '<span class="mhp-mini-chip" title="' + escapeHtml(m.drug_name || "") + '">'
                + escapeHtml(m.drug_name || "Rx") + "</span>";
        });
        if (meds.length > 4) html += '<span class="mhp-mini-more">+' + (meds.length - 4) + "</span>";
        html += "</div>";
        return html;
    }

    function renderThumbsMini(attachments) {
        if (!attachments || !attachments.length) return "";
        var images = attachments.filter(isImageAttachment);
        if (!images.length) return "";
        var html = '<div class="mhp-mini-row"><span class="mhp-mini-label"><i class="bi bi-images"></i></span><div class="mhp-mini-thumbs">';
        images.slice(0, 5).forEach(function (att) {
            var url = "/api/attachments/view/" + att.id;
            var attPayload = encodeURIComponent(JSON.stringify({
                id: att.id,
                file_path: att.file_path || "",
                original_filename: att.original_filename || att.filename || "",
            }));
            html += '<img src="' + url + '" class="mhp-mini-thumb" alt="" loading="lazy" '
                + 'onclick="MHP.openAttachmentFromEl(this, event)" '
                + 'data-attachment="' + attPayload + '">';
        });
        if (images.length > 5) html += '<span class="mhp-mini-more">+' + (images.length - 5) + "</span>";
        html += "</div></div>";
        return html;
    }

    function renderVisitsSection(appointments) {
        if (!appointments || !appointments.length) {
            return '<p class="mhp-empty"><i class="bi bi-calendar-x me-1"></i>No visits recorded</p>';
        }
        var html = '<div class="mhp-visits-list">';
        appointments.forEach(function (appt) {
            var href = "/doctor/appointments/" + appt.id;
            var dx = appt.consultation_note && appt.consultation_note.diagnosis
                ? escapeHtml(appt.consultation_note.diagnosis) : "";
            html += '<a href="' + href + '" class="mhp-visit-card">';
            html += '<div class="mhp-visit-head">';
            html += '<div><p class="mhp-visit-title">Visit #' + appt.id;
            if (appt.is_followup) html += ' <i class="bi bi-arrow-return-right text-info" title="Follow-up"></i>';
            html += "</p>";
            html += '<div class="mhp-visit-meta">';
            html += '<span><i class="bi bi-calendar3 me-1"></i>' + escapeHtml(fmtDate(appt.date)) + "</span>";
            if (appt.start_time) html += '<span><i class="bi bi-clock me-1"></i>' + escapeHtml(fmtTime(appt.start_time)) + "</span>";
            if (appt.doctor_display_name || appt.doctor_name) {
                html += '<span><i class="bi bi-person-badge me-1"></i>'
                    + escapeHtml(appt.doctor_display_name || appt.doctor_name) + "</span>";
            }
            if (appt.visit_type) html += '<span>' + escapeHtml(appt.visit_type) + "</span>";
            if (dx) html += '<span class="text-danger">' + dx + "</span>";
            html += "</div></div>";
            html += '<span class="mhp-visit-badge ' + statusBadgeClass(appt.status) + '">'
                + escapeHtml(appt.status || "—") + "</span>";
            html += "</div>";
            var mini = renderMedsMini(appt.medications)
                + renderGlassesMini(appt.glasses)
                + renderThumbsMini(appt.attachments);
            if (mini) html += '<div class="mhp-visit-mini" onclick="event.preventDefault(); event.stopPropagation();">' + mini + "</div>";
            html += "</a>";
        });
        html += "</div>";
        return html;
    }

    function renderPatientHistoryPopover(body, history, appointments) {
        var html = "";
        html += '<h4 class="mhp-section-title"><i class="bi bi-clipboard-heart"></i> Medical History</h4>';
        html += renderMedicalHistorySection(history);
        html += '<h4 class="mhp-section-title"><i class="bi bi-calendar-check"></i> Visits</h4>';
        html += renderVisitsSection(appointments);
        body.innerHTML = html;
    }

    function updatePopoverPosition(buttonElement, popoverContent) {
        var viewportWidth = global.innerWidth;
        var viewportHeight = global.innerHeight;
        popoverContent.style.width = Math.min(520, viewportWidth - 40) + "px";
        popoverContent.style.maxHeight = Math.min(680, viewportHeight - 40) + "px";
    }

    function showMedicalHistoryPopover(patientId, buttonElement, options) {
        options = options || {};
        var existing = document.getElementById("medicalHistoryPopover");
        if (existing) existing.remove();

        var popover = document.createElement("div");
        popover.id = "medicalHistoryPopover";
        popover.className = "medical-history-popover";

        var backdrop = document.createElement("div");
        backdrop.className = "medical-history-popover-backdrop";
        backdrop.onclick = closeMedicalHistoryPopover;

        var content = document.createElement("div");
        content.className = "medical-history-popover-content";

        var header = document.createElement("div");
        header.className = "medical-history-popover-header";
        header.innerHTML =
            '<h3><i class="bi bi-clipboard-heart me-2"></i>History</h3>'
            + '<button type="button" class="btn-close-popover" onclick="closeMedicalHistoryPopover()" aria-label="Close">'
            + '<i class="bi bi-x-lg"></i></button>';

        var body = document.createElement("div");
        body.className = "medical-history-popover-body";
        body.innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status">'
            + '<span class="visually-hidden">Loading...</span></div></div>';

        content.appendChild(header);
        content.appendChild(body);
        popover.appendChild(backdrop);
        popover.appendChild(content);
        document.body.appendChild(popover);

        if (buttonElement) updatePopoverPosition(buttonElement, content);

        var historyUrl = "/api/patients/" + patientId + "/medical-history";
        var visitsUrl = "/api/patients/" + patientId + "/appointments/history";
        if (options.excludeAppointmentId) {
            visitsUrl += "?exclude=" + encodeURIComponent(options.excludeAppointmentId);
        }

        Promise.all([
            fetch(historyUrl, { credentials: "same-origin" }).then(function (r) { return r.json(); }),
            fetch(visitsUrl, { credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } })
                .then(function (r) { return r.json(); }),
        ])
            .then(function (results) {
                var histRes = results[0];
                var apptRes = results[1];
                var history = (histRes.success && histRes.data) ? histRes.data : [];
                var appointments = (apptRes.ok || apptRes.success) && apptRes.data ? apptRes.data : [];
                if (!history.length && !appointments.length) {
                    body.innerHTML = '<div class="mhp-empty"><i class="bi bi-inbox me-2"></i>No history or visits found</div>';
                    return;
                }
                renderPatientHistoryPopover(body, history, appointments);
            })
            .catch(function (err) {
                console.error("Error loading patient history popover:", err);
                body.innerHTML =
                    '<div class="mhp-empty text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading history</div>';
            });

        var updatePosition = function () {
            if (buttonElement) updatePopoverPosition(buttonElement, content);
        };
        global.addEventListener("scroll", updatePosition, true);
        global.addEventListener("resize", updatePosition);
        popover._cleanup = function () {
            global.removeEventListener("scroll", updatePosition, true);
            global.removeEventListener("resize", updatePosition);
        };
    }

    function closeMedicalHistoryPopover() {
        var popover = document.getElementById("medicalHistoryPopover");
        if (popover) {
            if (popover._cleanup) popover._cleanup();
            popover.remove();
        }
    }

    function openAttachmentFromEl(imgEl, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        try {
            var raw = imgEl.getAttribute("data-attachment");
            if (!raw) return;
            var att = JSON.parse(decodeURIComponent(raw));
            openAttachment(att, e);
        } catch (_) {}
    }

    global.showMedicalHistoryPopover = showMedicalHistoryPopover;
    global.closeMedicalHistoryPopover = closeMedicalHistoryPopover;
    global.MHP = { openAttachmentFromEl: openAttachmentFromEl };

    global.addEventListener("beforeunload", closeMedicalHistoryPopover);
})(window);
