/**
 * AI Chat Widget
 * Floating chat widget for patient and appointment pages
 */

let aiChatWidget = {
    patientId: null,
    appointmentId: null,
    isOpen: false,
    isExpanded: false,
    chatHistory: [],
    widgetElement: null,
    chatContainer: null,
    messageInput: null
};

/**
 * Initialize AI Chat Widget
 */
function initAIChatWidget(patientId, appointmentId) {
    aiChatWidget.patientId = patientId;
    aiChatWidget.appointmentId = appointmentId;

    // Mark this page as having the AI agent so the mobile dock's CSS can
    // lift its baseline one slot to stack ABOVE the AI button. Pages WITHOUT
    // the AI widget (dashboard, calendar, etc.) leave this class absent and
    // the dock sits at the bottom anchor where back-to-top would appear.
    document.body.classList.add('has-ai-widget');

    createChatWidget();
    loadChatHistory();
}

/**
 * Create chat widget HTML structure
 */
function createChatWidget() {
    // Remove existing widget if any
    const existingWidget = document.getElementById('aiChatWidget');
    if (existingWidget) {
        existingWidget.remove();
    }
    // Remove existing modal if any
    const existingModal = document.getElementById('aiChatClearConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }

    const widgetHtml = `
        <div id="aiChatWidget" class="ai-chat-widget">
            <!-- Chat Bubble Button -->
            <button id="aiChatToggle" class="ai-chat-toggle" onclick="toggleAIChatWidget()" aria-label="Open Medical AI Chat">
                <span class="ai-chat-icon-wrapper">
                    <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                    <i class="bi bi-robot ai-chat-icon-robot"></i>
                </span>
            </button>

            <!-- Chat Window -->
            <div id="aiChatWindow" class="ai-chat-window" style="display: none;">
                <div class="ai-chat-header">
                    <div class="ai-chat-header-content">
                        <span class="ai-chat-icon-wrapper ai-chat-icon-wrapper-header">
                            <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                            <i class="bi bi-robot ai-chat-icon-robot"></i>
                        </span>
                        <span class="ms-2">Medical AI Assistant</span>
                    </div>
                    <div class="ai-chat-header-actions">
                        <button class="ai-chat-expand" onclick="toggleAIChatExpand()" aria-label="Expand Chat" title="Expand Chat">
                            <i class="bi bi-arrows-angle-expand" id="aiChatExpandIcon"></i>
                        </button>
                        <button class="ai-chat-close" onclick="toggleAIChatWidget()" aria-label="Close Chat" title="Close Chat">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- AI Disclaimer -->
                <div class="ai-chat-disclaimer">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span>AI Assistant: This tool provides guidance and suggestions only. Always use your clinical judgment and verify all information. AI responses should not replace professional medical decision-making.</span>
                </div>

                <!-- Quick Action Buttons -->
                <div class="ai-chat-actions">
                    <button class="ai-chat-action-btn" onclick="handleQuickAction('patient_history')" title="Send Patient History">
                        <i class="bi bi-file-medical me-1"></i>
                        <span>Send Patient History</span>
                    </button>
                    ${aiChatWidget.appointmentId ? `
                    <button class="ai-chat-action-btn" onclick="handleQuickAction('consultation_summary')" title="Summarize Consultation">
                        <i class="bi bi-clipboard-pulse me-1"></i>
                        <span>Summarize Consultation</span>
                    </button>
                    ` : ''}
                    <button class="ai-chat-action-btn" onclick="showClearChatConfirmModal()" title="Clear Chat">
                        <i class="bi bi-trash me-1"></i>
                        <span>Clear Chat</span>
                    </button>
                </div>

                <!-- Chat Messages Container -->
                <div id="aiChatMessages" class="ai-chat-messages">
                    <div class="ai-chat-welcome">
                        <span class="ai-chat-icon-wrapper ai-chat-icon-wrapper-welcome">
                            <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                            <i class="bi bi-robot ai-chat-icon-robot"></i>
                        </span>
                        <p>How can I assist you today?</p>
                        <small>Use the buttons above for quick actions or type your question below.</small>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="ai-chat-input-area">
                    <textarea 
                        id="aiChatInput" 
                        class="ai-chat-input" 
                        placeholder="Type your message..."
                        rows="1"
                        onkeydown="handleChatInputKeydown(event)"
                    ></textarea>
                    <button id="aiChatSend" class="ai-chat-send" onclick="sendAIChatMessage()" disabled>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    const modalHtml = `
            <!-- Clear Chat Confirmation Modal -->
            <div class="modal fade" id="aiChatClearConfirmModal" tabindex="-1" aria-labelledby="aiChatClearConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="aiChatClearConfirmModalLabel">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                Clear Chat History
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to clear all chat history for this patient?</p>
                            <p class="text-muted mb-0"><small>This will permanently delete all chat history across all appointments for this patient. This action cannot be undone.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="confirmClearAIChatHistory()">
                                <i class="bi bi-trash me-1"></i>
                                Clear History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    `;

    document.body.insertAdjacentHTML('beforeend', widgetHtml);
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    aiChatWidget.widgetElement = document.getElementById('aiChatWidget');
    aiChatWidget.chatContainer = document.getElementById('aiChatMessages');
    aiChatWidget.messageInput = document.getElementById('aiChatInput');
    
    // Auto-resize textarea
    aiChatWidget.messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        const sendBtn = document.getElementById('aiChatSend');
        sendBtn.disabled = !this.value.trim();
    });
}

/**
 * Toggle chat widget visibility
 */
function toggleAIChatWidget() {
    const chatWindow = document.getElementById('aiChatWindow');
    const toggleBtn = document.getElementById('aiChatToggle');
    
    if (!chatWindow || !toggleBtn) return;
    
    aiChatWidget.isOpen = !aiChatWidget.isOpen;
    
    if (aiChatWidget.isOpen) {
        chatWindow.style.display = 'flex';
        toggleBtn.classList.add('active');
        // Focus input after animation
        setTimeout(() => {
            aiChatWidget.messageInput?.focus();
        }, 300);
    } else {
        chatWindow.style.display = 'none';
        toggleBtn.classList.remove('active');
        // Reset expanded state when closing
        if (aiChatWidget.isExpanded) {
            toggleAIChatExpand();
        }
    }
}

/**
 * Toggle chat widget expanded state
 */
function toggleAIChatExpand() {
    const chatWindow = document.getElementById('aiChatWindow');
    const expandIcon = document.getElementById('aiChatExpandIcon');
    
    if (!chatWindow || !expandIcon) return;
    
    aiChatWidget.isExpanded = !aiChatWidget.isExpanded;
    
    if (aiChatWidget.isExpanded) {
        chatWindow.classList.add('ai-chat-window-expanded');
        expandIcon.classList.remove('bi-arrows-angle-expand');
        expandIcon.classList.add('bi-arrows-angle-contract');
        expandIcon.parentElement.setAttribute('title', 'Collapse Chat');
        expandIcon.parentElement.setAttribute('aria-label', 'Collapse Chat');
    } else {
        chatWindow.classList.remove('ai-chat-window-expanded');
        expandIcon.classList.remove('bi-arrows-angle-contract');
        expandIcon.classList.add('bi-arrows-angle-expand');
        expandIcon.parentElement.setAttribute('title', 'Expand Chat');
        expandIcon.parentElement.setAttribute('aria-label', 'Expand Chat');
    }
    
    // Scroll to bottom after resize
    setTimeout(() => {
        scrollChatToBottom();
    }, 100);
}

/**
 * Load chat history from server
 * Chat history is shared by patient_id only (appointment_id is ignored)
 */
async function loadChatHistory() {
    try {
        const params = new URLSearchParams();
        if (aiChatWidget.patientId) {
            params.append('patient_id', aiChatWidget.patientId);
        }
        // Note: appointment_id is NOT sent - chat history is shared by patient_id only

        const response = await fetch(`/api/ai/chat/history?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Failed to load chat history');
        }

        const data = await response.json();
        
        if (data.ok && data.data && data.data.length > 0) {
            aiChatWidget.chatHistory = data.data;
            renderChatHistory();
        }
    } catch (error) {
        // Silently handle error - chat history loading failure shouldn't break the widget
    }
}

/**
 * Render chat history
 */
function renderChatHistory() {
    if (!aiChatWidget.chatContainer) return;
    
    // Remove welcome message
    const welcome = aiChatWidget.chatContainer.querySelector('.ai-chat-welcome');
    if (welcome) {
        welcome.remove();
    }

    // Clear existing messages (except welcome)
    const existingMessages = aiChatWidget.chatContainer.querySelectorAll('.ai-chat-message');
    existingMessages.forEach(msg => msg.remove());

    // Render messages
    aiChatWidget.chatHistory.forEach(msg => {
        displayAIChatMessage(msg.role, msg.message, false);
    });

    // Scroll to bottom
    scrollChatToBottom();
}

/**
 * Display a chat message
 */
function displayAIChatMessage(role, content, addToHistory = true) {
    if (!aiChatWidget.chatContainer) return;

    // Remove welcome message if exists
    const welcome = aiChatWidget.chatContainer.querySelector('.ai-chat-welcome');
    if (welcome) {
        welcome.remove();
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `ai-chat-message ai-chat-message-${role}`;
    
    const timestamp = new Date().toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });

    if (role === 'user') {
        messageDiv.innerHTML = `
            <div class="ai-chat-message-content">
                <div class="ai-chat-message-text">${escapeHtml(content)}</div>
                <div class="ai-chat-message-time">${timestamp}</div>
            </div>
            <div class="ai-chat-message-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="ai-chat-message-avatar">
                <span class="ai-chat-icon-wrapper ai-chat-icon-wrapper-avatar">
                    <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                    <i class="bi bi-robot ai-chat-icon-robot"></i>
                </span>
            </div>
            <div class="ai-chat-message-content">
                <div class="ai-chat-message-text">${formatMarkdown(content)}</div>
                <div class="ai-chat-message-time">${timestamp}</div>
                <button class="ai-chat-copy-btn" onclick="copyChatMessage(this)" title="Copy message">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        `;
    }

    aiChatWidget.chatContainer.appendChild(messageDiv);
    
    if (addToHistory) {
        aiChatWidget.chatHistory.push({
            role: role,
            message: content,
            created_at: new Date().toISOString()
        });
    }

    scrollChatToBottom();
}

/**
 * Scroll chat to bottom
 */
function scrollChatToBottom() {
    if (aiChatWidget.chatContainer) {
        aiChatWidget.chatContainer.scrollTop = aiChatWidget.chatContainer.scrollHeight;
    }
}

/**
 * Handle quick action buttons
 */
async function handleQuickAction(actionType) {
    if (actionType === 'patient_history') {
        if (!aiChatWidget.patientId) {
            showAIChatNotification('Patient ID is required', 'error');
            return;
        }
        
        // Send explicit prompt requesting full analysis
        // IMPORTANT: The backend will automatically include the complete patient history context
        // This prompt asks the AI to analyze ONLY the data provided in the context
        const prompt = 'Based on the complete patient medical history provided in the context above, please provide a comprehensive analysis. CRITICAL: Use ONLY the information provided in the context. Do NOT invent or assume any information. If data is missing, state that it is not available.\n\n' +
                      'Please analyze:\n' +
                      '1. Key medical conditions and their progression (based on actual consultation notes and medical history entries)\n' +
                      '2. Medication history and patterns (based on actual prescriptions)\n' +
                      '3. Consultation notes and diagnoses over time (based on actual appointment records)\n' +
                      '4. Lab tests and radiology results (based on actual test records)\n' +
                      '5. Glasses prescriptions if applicable (based on actual prescription records)\n' +
                      '6. Any notable patterns or concerns (based on actual data)\n' +
                      '7. Recommendations for follow-up care (based on actual findings)\n\n' +
                      'Remember: Base your analysis STRICTLY on the data provided. If information is not in the context, state that it is not available.';
        
        // Show user message (short version for display)
        displayAIChatMessage('user', 'Analyze complete patient history', true);
        
        // Send the full detailed prompt to backend (skip displaying user message again)
        // The backend will build the full patient history context automatically
        await sendAIChatMessage(prompt, 'patient_history', true);
        
    } else if (actionType === 'consultation_summary') {
        if (!aiChatWidget.appointmentId) {
            showAIChatNotification('Appointment ID is required', 'error');
            return;
        }
        
        // Send explicit prompt requesting consultation summary
        // IMPORTANT: The backend will automatically include the consultation context and patient history
        // This prompt asks the AI to summarize ONLY the data provided in the context
        const prompt = 'Based on the consultation data and patient history provided in the context above, please provide a comprehensive summary. CRITICAL: Use ONLY the information provided in the context. Do NOT invent or assume any information. If data is missing, state that it is not available.\n\n' +
                      'Please summarize:\n' +
                      '1. Chief complaint and presenting symptoms (from actual consultation notes)\n' +
                      '2. Clinical findings and examination results (from actual examination data)\n' +
                      '3. Diagnosis and assessment (from actual diagnosis field)\n' +
                      '4. Treatment plan and prescribed medications (from actual plan and prescriptions)\n' +
                      '5. Lab tests or radiology ordered (from actual lab test records)\n' +
                      '6. Follow-up recommendations (from actual follow-up days or plan)\n' +
                      '7. Context from patient\'s previous medical history relevant to this visit (from actual history)\n\n' +
                      'Remember: Base your summary STRICTLY on the data provided. If information is not in the context, state that it is not available.';
        
        // Show user message (short version for display)
        displayAIChatMessage('user', 'Summarize consultation', true);
        
        // Send the full detailed prompt to backend (skip displaying user message again)
        // The backend will build the consultation summary context automatically
        await sendAIChatMessage(prompt, 'consultation_summary', true);
    }
}

/**
 * Send chat message
 */
async function sendAIChatMessage(message = null, contextType = 'general', skipUserDisplay = false) {
    const input = aiChatWidget.messageInput;
    const sendBtn = document.getElementById('aiChatSend');
    
    const messageText = message || (input ? input.value.trim() : '');
    
    if (!messageText) {
        return;
    }

    // Clear input if using textarea
    if (!message && input) {
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
    }

    // Remove loading message if exists (from quick actions)
    const loadingMsg = aiChatWidget.chatContainer.querySelector('.ai-chat-message-loading');
    if (loadingMsg) {
        loadingMsg.remove();
    }

    // Display user message (unless already displayed by quick action)
    if (!skipUserDisplay) {
        displayAIChatMessage('user', messageText, true);
    }

    // Show loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'ai-chat-message ai-chat-message-assistant ai-chat-message-loading';
    loadingDiv.innerHTML = `
        <div class="ai-chat-message-avatar">
            <span class="ai-chat-icon-wrapper ai-chat-icon-wrapper-avatar">
                <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                <i class="bi bi-robot ai-chat-icon-robot"></i>
            </span>
        </div>
        <div class="ai-chat-message-content">
            <div class="ai-chat-message-text">
                <div class="ai-chat-typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    `;
    aiChatWidget.chatContainer.appendChild(loadingDiv);
    scrollChatToBottom();

    try {
        const requestData = {
            message: messageText,
            patient_id: aiChatWidget.patientId,
            appointment_id: aiChatWidget.appointmentId,
            context_type: contextType
        };
        
        const response = await fetch('/api/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(requestData)
        });

        // Remove loading indicator
        if (loadingDiv.parentNode) {
            loadingDiv.remove();
        }

        // Read response as text first (so we can parse it once)
        const responseText = await response.text();
        
        if (!response.ok) {
            let errorMessage = 'Failed to send message';
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
            } catch (parseError) {
                // If JSON parsing fails, use the text response or status
                errorMessage = responseText || `HTTP ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }

        // Parse JSON response
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            throw new Error('Invalid response format from server');
        }
        
        if (data.ok && data.data && data.data.message) {
            displayAIChatMessage('assistant', data.data.message, true);
        } else {
            throw new Error(data.error || 'Failed to get response');
        }

    } catch (error) {
        // Remove loading indicator
        if (loadingDiv.parentNode) {
            loadingDiv.remove();
        }
        
        // Display error message (sanitize to prevent XSS)
        const errorMsg = error.message || 'Unknown error occurred';
        displayAIChatMessage('assistant', `Error: ${errorMsg}. Please try again.`, true);
        showAIChatNotification('Failed to send message. Please try again.', 'error');
    }
}

/**
 * Handle input keydown event
 */
function handleChatInputKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendAIChatMessage();
    }
}

/**
 * Show clear chat confirmation modal
 */
function showClearChatConfirmModal() {
    const modalElement = document.getElementById('aiChatClearConfirmModal');
    if (!modalElement) return;
    
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: true,
        keyboard: true
    });
    modal.show();
}

/**
 * Confirm and clear chat history
 */
async function confirmClearAIChatHistory() {
    // Close the modal
    const modalElement = document.getElementById('aiChatClearConfirmModal');
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }

    try {
        const response = await fetch('/api/ai/chat/history', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                patient_id: aiChatWidget.patientId
                // Note: appointment_id is NOT sent - clears all history for patient
            })
        });

        if (!response.ok) {
            throw new Error('Failed to clear chat history');
        }

        // Clear local history
        aiChatWidget.chatHistory = [];
        
        // Clear chat container
        if (aiChatWidget.chatContainer) {
            aiChatWidget.chatContainer.innerHTML = `
                <div class="ai-chat-welcome">
                    <span class="ai-chat-icon-wrapper ai-chat-icon-wrapper-welcome">
                        <i class="bi bi-heart-pulse ai-chat-icon-medical"></i>
                        <i class="bi bi-robot ai-chat-icon-robot"></i>
                    </span>
                    <p>Chat history cleared</p>
                    <small>How can I assist you today?</small>
                </div>
            `;
        }

        showAIChatNotification('Chat history cleared', 'success');

    } catch (error) {
        showAIChatNotification('Failed to clear chat history', 'error');
    }
}

/**
 * Copy chat message to clipboard
 */
function copyChatMessage(button) {
    const messageContent = button.closest('.ai-chat-message-content')
        .querySelector('.ai-chat-message-text');
    
    if (!messageContent) return;

    const text = messageContent.textContent || messageContent.innerText;
    
    navigator.clipboard.writeText(text).then(() => {
        const icon = button.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bi bi-check';
        button.classList.add('copied');
        
        setTimeout(() => {
            icon.className = originalClass;
            button.classList.remove('copied');
        }, 2000);
    }).catch(() => {
        // Silently handle copy failure
    });
}

/**
 * Format markdown-like text for display
 */
function formatMarkdown(text) {
    if (!text) return '';
    
    // Escape HTML first
    let html = escapeHtml(text);
    
    // Convert markdown-style formatting
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/`(.*?)`/g, '<code>$1</code>');
    
    // Convert line breaks
    html = html.replace(/\n/g, '<br>');
    
    // Convert numbered lists
    html = html.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');
    if (html.includes('<li>')) {
        html = '<ol>' + html.replace(/<li>/g, '<li>') + '</ol>';
    }
    
    return html;
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show notification in chat widget
 */
function showAIChatNotification(message, type = 'info') {
    // Use existing notification system if available
    if (typeof showNotification === 'function') {
        showNotification(message, type);
    }
    // Silently fail if notification system is not available
}
