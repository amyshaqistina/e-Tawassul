@extends('layouts.student')
@section('title', 'Submit Crisis Report')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
.crisis-wizard { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; min-width: 0 !important; max-width: 100% !important; }
.crisis-wizard > * { min-width: 0 !important; }

/* ===== Toasts ===== */
.toast-container { position: fixed !important; top: 24px !important; right: 24px !important; z-index: 9999 !important; display: flex !important; flex-direction: column !important; gap: 10px !important; pointer-events: none !important; }
.toast { background: #fff !important; border-radius: 12px !important; padding: 14px 18px !important; box-shadow: 0 12px 32px rgba(0,0,0,0.18) !important; display: flex !important; align-items: flex-start !important; gap: 12px !important; min-width: 280px !important; max-width: 380px !important; border-left: 4px solid #1a56db !important; animation: toast-in 0.3s ease-out !important; pointer-events: auto !important; }
.toast.toast-warning { border-left-color: #f59e0b !important; }
.toast.toast-error { border-left-color: #dc2626 !important; }
.toast.toast-success { border-left-color: #059669 !important; }
.toast.fade-out { animation: toast-out 0.3s ease-in forwards !important; }
@keyframes toast-in { from {opacity:0;transform:translateX(50px);} to {opacity:1;transform:translateX(0);} }
@keyframes toast-out { from {opacity:1;transform:translateX(0);} to {opacity:0;transform:translateX(50px);} }
.toast-icon { font-size: 22px !important; flex-shrink: 0 !important; line-height: 1 !important; }
.toast.toast-warning .toast-icon { color: #f59e0b !important; }
.toast.toast-error .toast-icon { color: #dc2626 !important; }
.toast.toast-success .toast-icon { color: #059669 !important; }
.toast-body { flex: 1; min-width: 0; }
.toast-title { font-size:13.5px !important; font-weight:700 !important; color:#0f172a !important; margin:0 0 2px 0 !important; }
.toast-msg { font-size:12.5px !important; color:#64748b !important; margin:0 !important; line-height:1.5 !important; }
.toast-close { background:transparent !important; border:none !important; color:#94a3b8 !important; cursor:pointer !important; padding:2px !important; font-size:16px !important; }

/* ===== Lightbox ===== */
.lightbox { position: fixed !important; inset: 0 !important; background: rgba(0,0,0,0.85) !important; z-index: 10000 !important; display: none !important; align-items: center !important; justify-content: center !important; padding: 40px !important; cursor: zoom-out !important; }
.lightbox.show { display: flex !important; }
.lightbox img { max-width: 100% !important; max-height: 100% !important; border-radius: 8px !important; box-shadow: 0 24px 60px rgba(0,0,0,0.5) !important; }
.lightbox-close { position: absolute !important; top: 20px !important; right: 24px !important; color: #fff !important; background: rgba(255,255,255,0.1) !important; border: 1px solid rgba(255,255,255,0.3) !important; border-radius: 50% !important; width: 36px !important; height: 36px !important; cursor: pointer !important; font-size: 18px !important; display: flex !important; align-items: center !important; justify-content: center !important; }

/* ===== Stepper ===== */
.wizard-stepper { display: flex !important; align-items: center !important; justify-content: space-between !important; background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 14px !important; padding: 22px 24px !important; margin-bottom: 20px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important; gap: 12px !important; flex-wrap: nowrap !important; }
.wizard-step { display: flex !important; align-items: center !important; gap: 10px !important; flex: 1 1 0 !important; min-width: 0 !important; position: relative !important; cursor: pointer !important; }
.wizard-step:not(:last-child)::after { content: ''; position: absolute; top: 18px; left: 46px; width: calc(100% - 56px); height: 2px; background: #e2e8f0; z-index: 0; }
.wizard-step.completed:not(:last-child)::after { background: #10b981; }
@media (max-width: 700px) { .wizard-step::after { display: none !important; } }
.wizard-step-num { width: 36px !important; height: 36px !important; border-radius: 50% !important; background: #f1f5f9 !important; color: #94a3b8 !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: 700 !important; font-size: 14px !important; flex-shrink: 0 !important; border: 2px solid transparent !important; transition: all 0.2s !important; z-index: 1 !important; position: relative !important; }
.wizard-step.active .wizard-step-num { background: #1a56db !important; color: #fff !important; border-color: #1a56db !important; box-shadow: 0 0 0 4px #dbeafe !important; }
.wizard-step.completed .wizard-step-num { background: #10b981 !important; color: #fff !important; border-color: #10b981 !important; }
.wizard-step-label { min-width: 0 !important; overflow: hidden !important; }
.wizard-step-label h6 { font-size: 13px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 0 2px 0 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
.wizard-step.active .wizard-step-label h6 { color: #1a56db !important; }
.wizard-step-label small { font-size: 11px !important; color: #94a3b8 !important; display: block !important; }
.wizard-step.completed .wizard-step-label small { color: #10b981 !important; font-weight: 600 !important; }

/* ===== Layout ===== */
.wizard-layout { display: block !important; }
.wizard-card { background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 14px !important; padding: 28px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important; }
.wizard-card h3 { font-size: 18px !important; font-weight: 800 !important; color: #0f172a !important; margin: 0 0 6px 0 !important; }
.wizard-card .subtitle { font-size: 13px !important; color: #64748b !important; margin: 0 0 22px 0 !important; }
.section-divider { margin: 28px 0 18px 0 !important; padding-top: 22px !important; border-top: 1px solid #f1f5f9 !important; }
.section-divider h4 { font-size: 15px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 0 4px 0 !important; }
.section-divider p { font-size: 12.5px !important; color: #64748b !important; margin: 0 0 14px 0 !important; }
.step-panel { display: none; }
.step-panel.active { display: block; }

/* ===== Type cards ===== */
.type-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important; margin-bottom: 8px !important; }
@media (max-width: 600px) { .type-grid { grid-template-columns: 1fr !important; } }
.type-card { background: #f8faff !important; border: 2px solid #e2e8f0 !important; border-radius: 12px !important; padding: 16px !important; cursor: pointer !important; transition: all 0.2s !important; display: flex !important; gap: 12px !important; align-items: center !important; }
.type-card:hover { border-color: #93c5fd !important; background: #eff6ff !important; }
.type-card.selected { border-color: #1a56db !important; background: #eff6ff !important; box-shadow: 0 0 0 4px #dbeafe !important; }
.type-card-icon { width: 42px !important; height: 42px !important; border-radius: 11px !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 20px !important; flex-shrink: 0 !important; }
.type-card[data-type="medical"] .type-card-icon { background: #fef2f2 !important; color: #dc2626 !important; }
.type-card[data-type="accident"] .type-card-icon { background: #fff7ed !important; color: #ea580c !important; }
.type-card[data-type="natural_disaster"] .type-card-icon { background: #eff6ff !important; color: #1a56db !important; }
.type-card[data-type="death"] .type-card-icon { background: #f5f3ff !important; color: #7c3aed !important; }
.type-card-text h5 { font-size: 14px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 0 2px 0 !important; }
.type-card-text p { font-size: 11px !important; color: #64748b !important; margin: 0 !important; line-height: 1.4 !important; }

/* ===== Sub-category pills ===== */
.subcat-section { display: none; }
.subcat-section.active { display: block; }
.subcat-grid { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important; gap: 10px !important; }
.subcat-pill { background: #ffffff !important; border: 1.5px solid #e2e8f0 !important; border-radius: 10px !important; padding: 12px 14px !important; cursor: pointer !important; transition: all 0.2s !important; display: flex !important; align-items: center !important; gap: 10px !important; }
.subcat-pill:hover { border-color: #93c5fd !important; background: #f8faff !important; }
.subcat-pill.selected { border-color: #1a56db !important; background: #eff6ff !important; }
.subcat-pill-radio { width: 18px !important; height: 18px !important; border-radius: 50% !important; border: 2px solid #cbd5e1 !important; flex-shrink: 0 !important; position: relative !important; }
.subcat-pill.selected .subcat-pill-radio { border-color: #1a56db !important; background: #1a56db !important; }
.subcat-pill.selected .subcat-pill-radio::after { content: ''; position: absolute; top: 3px; left: 3px; width: 8px; height: 8px; border-radius: 50%; background: #fff; }
.subcat-pill-content { flex: 1; min-width: 0; }
.subcat-pill-label { font-size: 13px !important; font-weight: 600 !important; color: #0f172a !important; }
.subcat-pill .malay-name { font-size: 10.5px !important; color: #94a3b8 !important; font-weight: 400 !important; margin-left: 4px !important; font-style: italic !important; }

/* ===== Contextual helpers ===== */
.ctx-helper { margin-top: 18px !important; border-radius: 12px !important; padding: 16px 18px !important; display: none; animation: fade-in 0.3s ease-out !important; }
.ctx-helper.show { display: block; }
@keyframes fade-in { from {opacity:0;transform:translateY(-5px);} to {opacity:1;transform:translateY(0);} }
.ctx-helper.mental { background: #fdf4ff !important; border: 1px solid #f0abfc !important; }
.ctx-helper.friend, .ctx-helper.road { background: #fff7ed !important; border: 1px solid #fdba74 !important; }
.ctx-helper.disaster { background: #eff6ff !important; border: 1px solid #bfdbfe !important; }
.ctx-helper-header { display: flex !important; align-items: center !important; gap: 10px !important; margin-bottom: 12px !important; }
.ctx-helper-header i { font-size: 20px !important; }
.ctx-helper.mental .ctx-helper-header i { color: #a21caf !important; }
.ctx-helper.friend .ctx-helper-header i, .ctx-helper.road .ctx-helper-header i { color: #c2410c !important; }
.ctx-helper.disaster .ctx-helper-header i { color: #1a56db !important; }
.ctx-helper-header h6 { font-size: 14px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 !important; }
.ctx-helper p { font-size: 12.5px !important; line-height: 1.55 !important; margin: 0 0 12px 0 !important; }
.ctx-helper.mental p { color: #86198f !important; }
.ctx-helper.friend p, .ctx-helper.road p { color: #9a3412 !important; }
.ctx-helper.disaster p { color: #1e3a8a !important; }
.ctx-helper-action-row { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; margin-top: 10px !important; }
.ctx-btn { display: inline-flex !important; align-items: center !important; gap: 6px !important; background: #fff !important; border: 1.5px solid !important; border-radius: 8px !important; padding: 7px 12px !important; font-size: 12px !important; font-weight: 600 !important; text-decoration: none !important; transition: all 0.2s !important; cursor: pointer !important; }
.ctx-helper.mental .ctx-btn { color: #a21caf !important; border-color: #f0abfc !important; }
.ctx-helper.friend .ctx-btn, .ctx-helper.road .ctx-btn { color: #c2410c !important; border-color: #fdba74 !important; }
.ctx-helper.disaster .ctx-btn { color: #1a56db !important; border-color: #bfdbfe !important; }
.ctx-btn:hover { background: currentColor !important; color: #fff !important; }
.ctx-helper.disaster .ctx-btn:hover { background: #1a56db !important; color: #fff !important; }
.ctx-extra-field { margin-top: 10px !important; }
.ctx-extra-field label { display: block !important; font-size: 11.5px !important; font-weight: 600 !important; margin-bottom: 4px !important; color: #0f172a !important; }
.ctx-extra-field input { width: 100% !important; padding: 8px 12px !important; border: 1.5px solid #e2e8f0 !important; border-radius: 8px !important; font-size: 13px !important; background: #fff !important; }

.live-warnings, .live-news { margin-top: 12px !important; }
.live-warnings-title, .live-news-title { font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; color: #1e3a8a !important; margin-bottom: 6px !important; display: flex !important; align-items: center !important; gap: 6px !important; }
.live-warning-item { background: #fff !important; border: 1px solid #bfdbfe !important; border-radius: 8px !important; padding: 8px 12px !important; margin-bottom: 6px !important; font-size: 12px !important; }
.live-warning-item .w-title { font-weight: 600 !important; color: #0f172a !important; }
.live-warning-item .w-meta { font-size: 11px !important; color: #64748b !important; margin-top: 2px !important; }
.live-news-item { display: flex !important; align-items: flex-start !important; gap: 8px !important; padding: 10px 0 !important; border-bottom: 1px solid #dbeafe !important; font-size: 12px !important; }
.live-news-item:last-child { border-bottom: none !important; }
.live-news-item .news-content { flex: 1; min-width: 0; }
.live-news-item a { color: #0f172a !important; font-weight: 500 !important; text-decoration: none !important; }
.live-news-item a:hover { color: #1a56db !important; text-decoration: underline !important; }
.live-news-item .n-meta { font-size: 10.5px !important; color: #64748b !important; margin-top: 4px !important; display: flex !important; flex-wrap: wrap !important; gap: 8px !important; align-items: center !important; }
.use-location-btn { background: #1a56db !important; color: #fff !important; border: none !important; border-radius: 4px !important; padding: 2px 8px !important; font-size: 10.5px !important; font-weight: 600 !important; cursor: pointer !important; display: inline-flex !important; align-items: center !important; gap: 4px !important; }
.use-location-btn:hover { background: #1245b8 !important; }
.live-loading { text-align: center !important; color: #94a3b8 !important; font-size: 11.5px !important; padding: 12px 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important; }

/* ===== Step 2 fields ===== */
.field-group { margin-bottom: 18px !important; }
.field-group label { display: block !important; font-size: 13px !important; font-weight: 600 !important; color: #0f172a !important; margin-bottom: 6px !important; }
.field-group label .req { color: #dc2626 !important; }
.field-group label .opt { font-size: 11px !important; color: #94a3b8 !important; font-weight: 500 !important; margin-left: 4px !important; }
.field-input, .field-textarea, .field-select { width: 100% !important; padding: 10px 14px !important; border: 1.5px solid #e2e8f0 !important; border-radius: 8px !important; font-size: 14px !important; background: #f8faff !important; transition: all 0.2s !important; font-family: inherit !important; }
.field-input:focus, .field-textarea:focus, .field-select:focus { outline: none !important; border-color: #1a56db !important; background: #fff !important; box-shadow: 0 0 0 3px rgba(26,86,219,0.08) !important; }
.field-textarea { resize: vertical !important; min-height: 100px !important; }
.field-row { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 14px !important; }
@media (max-width: 600px) { .field-row { grid-template-columns: 1fr !important; } }

.location-input-wrap { position: relative !important; }
.location-input-wrap .field-input { padding-right: 200px !important; }
.location-detect-btn, .location-search-btn { position: absolute !important; top: 50% !important; transform: translateY(-50%) !important; color: #fff !important; border: none !important; border-radius: 6px !important; padding: 6px 12px !important; font-size: 12px !important; font-weight: 600 !important; cursor: pointer !important; display: inline-flex !important; align-items: center !important; gap: 5px !important; }
.location-detect-btn { right: 6px !important; background: #1a56db !important; }
.location-detect-btn:hover { background: #1245b8 !important; }
.location-search-btn { right: 100px !important; background: #64748b !important; }
.location-search-btn:hover { background: #475569 !important; }
.location-detect-btn:disabled, .location-search-btn:disabled { opacity: 0.6 !important; cursor: not-allowed !important; }
@media (max-width: 600px) {
    .location-input-wrap .field-input { padding-right: 14px !important; padding-bottom: 50px !important; }
    .location-detect-btn, .location-search-btn { top: auto !important; bottom: 6px !important; transform: none !important; }
    .location-search-btn { left: 6px !important; right: auto !important; }
}
.location-status { font-size: 11.5px !important; margin-top: 6px !important; display: none !important; align-items: center !important; gap: 6px !important; }
.location-status.show { display: flex !important; }
.location-status.success { color: #059669 !important; }
.location-status.error { color: #dc2626 !important; }
.location-status.loading { color: #1a56db !important; }
#map-preview { width: 100% !important; height: 240px !important; border-radius: 10px !important; margin-top: 10px !important; border: 1px solid #e2e8f0 !important; display: none !important; }
#map-preview.show { display: block !important; }

.file-dropzone { background: #f8faff !important; border: 2px dashed #cbd5e1 !important; border-radius: 12px !important; padding: 28px 20px !important; text-align: center !important; cursor: pointer !important; transition: all 0.2s !important; display: block !important; }
.file-dropzone:hover, .file-dropzone.dragover { border-color: #1a56db !important; background: #eff6ff !important; }
.file-dropzone.error-shake { border-color: #dc2626 !important; background: #fef2f2 !important; animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both !important; }
@keyframes shake { 10%, 90% { transform: translateX(-2px); } 20%, 80% { transform: translateX(3px); } 30%, 50%, 70% { transform: translateX(-4px); } 40%, 60% { transform: translateX(4px); } }
.file-dropzone i { font-size: 32px !important; color: #1a56db !important; display: block !important; margin-bottom: 8px !important; }
.file-dropzone p { font-size: 13.5px !important; font-weight: 600 !important; color: #0f172a !important; margin: 0 0 4px 0 !important; }
.file-dropzone small { font-size: 11px !important; color: #64748b !important; }
.file-list { margin-top: 14px !important; display: grid !important; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)) !important; gap: 10px !important; }
.file-item { background: #fff !important; border: 1px solid #e2e8f0 !important; border-radius: 10px !important; padding: 10px !important; position: relative !important; display: flex !important; flex-direction: column !important; gap: 8px !important; }
.file-item-preview { width: 100% !important; height: 90px !important; border-radius: 8px !important; background: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; overflow: hidden !important; }
.file-item-preview img { width: 100% !important; height: 100% !important; object-fit: cover !important; cursor: pointer !important; }
.file-item-preview .file-icon-fallback { font-size: 36px !important; color: #1a56db !important; }
.file-item-info { display: flex !important; flex-direction: column !important; gap: 2px !important; min-width: 0 !important; }
.file-item .file-name { font-size: 12px !important; color: #0f172a !important; font-weight: 500 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
.file-item .file-size { font-size: 10.5px !important; color: #94a3b8 !important; }
.file-item .file-remove { position: absolute !important; top: 6px !important; right: 6px !important; background: rgba(220,38,38,0.9) !important; color: #fff !important; border: none !important; border-radius: 50% !important; width: 22px !important; height: 22px !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 13px !important; }
.file-item .file-remove:hover { background: #dc2626 !important; }

.suggested-docs { background: #ecfdf5 !important; border: 1px solid #a7f3d0 !important; border-radius: 10px !important; padding: 12px 14px !important; margin-bottom: 14px !important; display: flex !important; gap: 10px !important; align-items: flex-start !important; }
.suggested-docs i { color: #059669 !important; font-size: 16px !important; flex-shrink: 0 !important; margin-top: 1px !important; }
.suggested-docs-body { flex: 1; min-width: 0; }
.suggested-docs-body h6 { font-size: 12px !important; font-weight: 700 !important; color: #065f46 !important; margin: 0 0 4px 0 !important; }
.suggested-docs-body p { font-size: 11.5px !important; color: #047857 !important; margin: 0 !important; line-height: 1.5 !important; }
.suggested-docs-body .docs-note { color: #059669 !important; font-style: italic !important; margin-top: 4px !important; font-size: 11px !important; }

/* ===== Step 3 review ===== */
.review-section { border-bottom: 1px solid #f1f5f9 !important; padding: 12px 0 !important; display: flex !important; align-items: flex-start !important; gap: 12px !important; }
.review-section:last-child { border-bottom: none !important; }
.review-icon { width: 32px !important; height: 32px !important; border-radius: 8px !important; background: #eff6ff !important; color: #1a56db !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; font-size: 14px !important; }
.review-content { flex: 1 !important; min-width: 0 !important; }
.review-label { font-size: 11px !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; font-weight: 600 !important; margin: 0 0 2px 0 !important; }
.review-value { font-size: 13.5px !important; color: #0f172a !important; font-weight: 500 !important; margin: 0 !important; word-break: break-word !important; }
.review-value.empty { color: #94a3b8 !important; font-style: italic !important; font-weight: 400 !important; }
.review-thumbs { display: flex !important; flex-wrap: wrap !important; gap: 8px !important; margin-top: 8px !important; }
.review-thumb { width: 70px !important; height: 70px !important; border-radius: 8px !important; overflow: hidden !important; border: 1px solid #e2e8f0 !important; cursor: pointer !important; background: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; }
.review-thumb img { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.review-thumb .fb-icon { font-size: 22px !important; color: #64748b !important; }
.consent-box { background: #eff6ff !important; border: 1px solid #bfdbfe !important; border-radius: 10px !important; padding: 14px 16px !important; margin-top: 18px !important; display: flex !important; gap: 10px !important; align-items: flex-start !important; }
.consent-box input[type="checkbox"] { margin-top: 3px !important; width: 16px !important; height: 16px !important; flex-shrink: 0 !important; }
.consent-box label { font-size: 12.5px !important; color: #1e3a8a !important; line-height: 1.5 !important; cursor: pointer !important; }

/* ===== Footer buttons ===== */
.wizard-footer { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-top: 24px !important; padding-top: 18px !important; border-top: 1px solid #f1f5f9 !important; gap: 10px !important; }
.btn-wizard { padding: 10px 20px !important; border-radius: 8px !important; font-size: 14px !important; font-weight: 700 !important; cursor: pointer !important; transition: all 0.2s !important; border: none !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; }
.btn-wizard-prev { background: #fff !important; color: #64748b !important; border: 1.5px solid #e2e8f0 !important; }
.btn-wizard-prev:hover { background: #f8fafc !important; }
.btn-wizard-next { background: #1a56db !important; color: #fff !important; }
.btn-wizard-next:hover { background: #1245b8 !important; }
.btn-wizard-submit { background: #059669 !important; color: #fff !important; }
.btn-wizard-submit:hover { background: #047857 !important; }
.btn-wizard:disabled { opacity: 0.5 !important; cursor: not-allowed !important; }
.btn-wizard[hidden] { display: none !important; }
</style>

<div class="toast-container" id="toast-container"></div>
<div class="lightbox" id="lightbox">
    <button type="button" class="lightbox-close" id="lightbox-close" aria-label="Close">&times;</button>
    <img id="lightbox-img" src="" alt="Preview">
</div>

<div class="crisis-wizard container-fluid pb-3">

    <div class="wizard-stepper">
        <div class="wizard-step active" data-step="1">
            <div class="wizard-step-num">1</div>
            <div class="wizard-step-label"><h6>Type &amp; Category</h6><small>Step 1 of 3</small></div>
        </div>
        <div class="wizard-step" data-step="2">
            <div class="wizard-step-num">2</div>
            <div class="wizard-step-label"><h6>Details &amp; Documents</h6><small>Step 2 of 3</small></div>
        </div>
        <div class="wizard-step" data-step="3">
            <div class="wizard-step-num">3</div>
            <div class="wizard-step-label"><h6>Review &amp; Submit</h6><small>Step 3 of 3</small></div>
        </div>
    </div>

    <form method="POST" action="{{ route('student.crisis.store') }}" enctype="multipart/form-data" id="crisis-form" novalidate>
        @csrf
        <input type="hidden" name="crisis_type"    id="crisis_type"    value="{{ old('crisis_type') }}">
        <input type="hidden" name="sub_category"   id="sub_category"   value="{{ old('sub_category') }}">
        <input type="hidden" name="impact_level"   id="impact_level"   value="medium">
        <input type="hidden" name="latitude"       id="latitude"       value="{{ old('latitude') }}">
        <input type="hidden" name="longitude"      id="longitude"      value="{{ old('longitude') }}">
        <input type="hidden" name="related_person" id="related_person" value="">
        <input type="hidden" name="police_report"  id="police_report"  value="">

        <div class="wizard-layout">
            <div class="wizard-card">

                {{-- ===== STEP 1 ===== --}}
                <div class="step-panel active" data-panel="1">
                    <h3>What kind of crisis are you reporting?</h3>
                    <p class="subtitle">Choose the category, then select what best describes your situation.</p>

                    <div class="type-grid">
                        <div class="type-card" data-type="medical">
                            <div class="type-card-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                            <div class="type-card-text"><h5>Medical Emergency</h5><p>Illness, hospitalization, mental health</p></div>
                        </div>
                        <div class="type-card" data-type="accident">
                            <div class="type-card-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
                            <div class="type-card-text"><h5>Accident</h5><p>Road, lab, sports, or personal injury</p></div>
                        </div>
                        <div class="type-card" data-type="natural_disaster">
                            <div class="type-card-icon"><i class="bi bi-cloud-rain-heavy-fill"></i></div>
                            <div class="type-card-text"><h5>Natural Disaster</h5><p>Banjir, tanah runtuh, kebakaran</p></div>
                        </div>
                        <div class="type-card" data-type="death">
                            <div class="type-card-icon"><i class="bi bi-flower3"></i></div>
                            <div class="type-card-text"><h5>Death / Bereavement</h5><p>Loss of family, friend, or loved one</p></div>
                        </div>
                    </div>

                    <div class="section-divider" id="subcat-section-wrapper" style="display:none;">
                        <h4>Sub-category</h4>
                        <p>Based on Malaysia Bencana (NADMA) classifications.</p>

                        <div class="subcat-section" data-parent="medical">
                            <div class="subcat-grid">
                                <div class="subcat-pill" data-value="sudden_illness"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Sudden Serious Illness</div></div></div>
                                <div class="subcat-pill" data-value="mental_health"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Mental Health Crisis</div></div></div>
                                <div class="subcat-pill" data-value="hospitalization"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Hospitalization</div></div></div>
                                <div class="subcat-pill" data-value="surgery_required"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Surgery Required</div></div></div>
                                <div class="subcat-pill" data-value="chronic_flare"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Chronic Condition Flare-up</div></div></div>
                                <div class="subcat-pill" data-value="family_critical_illness"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Family Member Critical Illness</div></div></div>
                            </div>
                        </div>

                        <div class="subcat-section" data-parent="accident">
                            <div class="subcat-grid">
                                <div class="subcat-pill" data-value="road_accident"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Road Accident <span class="malay-name">(Kemalangan Jalan Raya)</span></div></div></div>
                                <div class="subcat-pill" data-value="lab_workshop"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Lab / Workshop Accident</div></div></div>
                                <div class="subcat-pill" data-value="sports_injury"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Sports Injury</div></div></div>
                                <div class="subcat-pill" data-value="fall_fracture"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Fall / Fracture</div></div></div>
                                <div class="subcat-pill" data-value="burn_electrical"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Burn / Electrical Injury</div></div></div>
                                <div class="subcat-pill" data-value="house_fire"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">House Fire <span class="malay-name">(Kebakaran Rumah)</span></div></div></div>
                                <div class="subcat-pill" data-value="drowning"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Drowning / Near-drowning</div></div></div>
                            </div>
                        </div>

                        <div class="subcat-section" data-parent="natural_disaster">
                            <div class="subcat-grid">
                                <div class="subcat-pill" data-value="flood"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Flood <span class="malay-name">(Banjir)</span></div></div></div>
                                <div class="subcat-pill" data-value="landslide"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Landslide <span class="malay-name">(Tanah Runtuh)</span></div></div></div>
                                <div class="subcat-pill" data-value="fire"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Forest / Building Fire <span class="malay-name">(Kebakaran)</span></div></div></div>
                                <div class="subcat-pill" data-value="storm"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Storm / Heavy Rain <span class="malay-name">(Ribut / Hujan Lebat)</span></div></div></div>
                                <div class="subcat-pill" data-value="haze"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Haze <span class="malay-name">(Jerebu)</span></div></div></div>
                                <div class="subcat-pill" data-value="earthquake"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Earthquake <span class="malay-name">(Gempa Bumi)</span></div></div></div>
                                <div class="subcat-pill" data-value="strong_wind"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Strong Wind <span class="malay-name">(Angin Kencang)</span></div></div></div>
                            </div>
                        </div>

                        <div class="subcat-section" data-parent="death">
                            <div class="subcat-grid">
                                <div class="subcat-pill" data-value="death_parent"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Parent <span class="malay-name">(Bapa / Ibu)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_sibling"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Sibling <span class="malay-name">(Adik-beradik)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_grandparent"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Grandparent <span class="malay-name">(Datuk / Nenek)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_relative"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Close Relative <span class="malay-name">(Pakcik / Makcik / Sepupu)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_guardian"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Guardian <span class="malay-name">(Penjaga Sah)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_spouse"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Spouse <span class="malay-name">(Suami / Isteri)</span></div></div></div>
                                <div class="subcat-pill" data-value="death_friend"><div class="subcat-pill-radio"></div><div class="subcat-pill-content"><div class="subcat-pill-label">Close Friend / Coursemate / Roommate</div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="ctx-helper mental" id="helper-mental">
                        <div class="ctx-helper-header">
                            <i class="bi bi-heart-fill"></i><h6>Support resources</h6>
                        </div>
                        <p>If you are in a mental health crisis, please reach out. Free, confidential help is available 24/7.</p>
                        <div class="ctx-helper-action-row">
                            <a href="tel:15999" class="ctx-btn"><i class="bi bi-telephone-fill"></i> Talian Kasih: 15999</a>
                            <a href="tel:0376272929" class="ctx-btn"><i class="bi bi-telephone-fill"></i> Befrienders: 03-7627 2929</a>
                            <a href="https://www.iium.edu.my/office/cscs" target="_blank" rel="noopener" class="ctx-btn"><i class="bi bi-box-arrow-up-right"></i> IIUM Counselling</a>
                        </div>
                    </div>

                    <div class="ctx-helper friend" id="helper-friend">
                        <div class="ctx-helper-header"><i class="bi bi-people-fill"></i><h6>Tell us about your friend</h6></div>
                        <p>If they were an IIUM student or staff, providing their details helps us coordinate support.</p>
                        <div class="ctx-extra-field">
                            <label>Name &amp; Student ID (if IIUM)</label>
                            <input type="text" id="related_person_input" placeholder="e.g. Ahmad bin Ali — 2234567" maxlength="200">
                        </div>
                    </div>

                    <div class="ctx-helper road" id="helper-road">
                        <div class="ctx-helper-header"><i class="bi bi-shield-fill-check"></i><h6>Police report helps verification</h6></div>
                        <p>If you have already filed a police report, the report number speeds up verification. It's optional — you can also submit it later.</p>
                        <div class="ctx-extra-field">
                            <label>Police Report Number <span style="color:#94a3b8;">(optional)</span></label>
                            <input type="text" id="police_report_input" placeholder="e.g. RPT/12345/2024" maxlength="50">
                        </div>
                    </div>

                    <div class="ctx-helper disaster" id="helper-disaster">
                        <div class="ctx-helper-header"><i class="bi bi-broadcast"></i><h6>Live disaster information</h6></div>
                        <p>Latest official warnings and news. Click "Use this location" on any headline matching your area to auto-fill the location field.</p>
                        <div id="live-data-container">
                            <div class="live-loading"><i class="bi bi-arrow-repeat"></i> Loading live information...</div>
                        </div>
                    </div>
                </div>

                {{-- ===== STEP 2 ===== --}}
                <div class="step-panel" data-panel="2">
                    <h3>Tell us what happened</h3>
                    <p class="subtitle">Provide the location, date, and a description of the incident.</p>

                    <div class="field-group">
                        <label>Where did it happen? <span class="req">*</span></label>
                        <div class="location-input-wrap">
                            <input type="text" name="location" id="location" class="field-input"
                                value="{{ old('location') }}"
                                placeholder="Type a location and click Search, or click Detect for your current location" required>
                            <button type="button" class="location-search-btn" id="search-location-btn" title="Search for the location you typed">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <button type="button" class="location-detect-btn" id="detect-location-btn" title="Use your current GPS location">
                                <i class="bi bi-geo-alt-fill"></i> Detect
                            </button>
                        </div>
                        <div class="location-status" id="location-status">
                            <i class="bi bi-info-circle"></i>
                            <span id="location-status-msg"></span>
                        </div>
                        <div id="map-preview"></div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label>Date <span class="req">*</span></label>
                            <input type="date" name="incident_date" id="incident_date" class="field-input"
                                value="{{ old('incident_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="field-group">
                            <label>Time <span class="req">*</span></label>
                            <input type="time" name="incident_time" id="incident_time" class="field-input"
                                value="{{ old('incident_time', date('H:i')) }}" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Please describe what happened <span class="req">*</span></label>
                        <textarea name="crisis_description" id="crisis_description"
                            class="field-textarea"
                            placeholder="Provide detailed information about what happened, who was involved, and any immediate actions taken..."
                            required minlength="10" maxlength="2000">{{ old('crisis_description') }}</textarea>
                        <small style="color:#94a3b8; font-size:11px;"><span id="desc-count">0</span> / 2000 (min 10)</small>
                    </div>

                    <div class="field-group">
                        <label>Any immediate actions taken? <span class="opt">(optional)</span></label>
                        <input type="text" name="immediate_actions" id="immediate_actions" class="field-input"
                            value="{{ old('immediate_actions') }}"
                            placeholder="e.g. Called security, provided first aid, moved to safe location...">
                    </div>

                    <div class="section-divider">
                        <h4>Supporting documents <span style="color:#dc2626">*</span></h4>
                        <p>Please upload at least one supporting document to help admin verify your report (max 5 files, 5MB each).</p>
                    </div>

                    <div class="suggested-docs" id="suggested-docs" style="display:none;">
                        <i class="bi bi-lightbulb-fill"></i>
                        <div class="suggested-docs-body">
                            <h6>💡 Suggested documents for this situation:</h6>
                            <p id="suggested-docs-list"></p>
                            <p class="docs-note">Upload at least one. If you have additional documents, you can add more from your case page later.</p>
                        </div>
                    </div>

                    <label for="file-input" class="file-dropzone" id="file-dropzone">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <p>Click to upload or drag and drop</p>
                        <small>JPG, PNG, PDF, DOC (Max 5MB per file) — <strong>at least 1 file required</strong></small>
                    </label>
                    <input type="file" name="supporting_evidence[]" id="file-input" multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;">
                    <div class="file-list" id="file-list"></div>
                </div>

                {{-- ===== STEP 3 ===== --}}
                <div class="step-panel" data-panel="3">
                    <h3>Review &amp; submit</h3>
                    <p class="subtitle">Please review your information before submitting.</p>

                    <div id="review-summary">
                        <div class="review-section">
                            <div class="review-icon"><i class="bi bi-tag-fill"></i></div>
                            <div class="review-content">
                                <p class="review-label">Crisis Type</p>
                                <p class="review-value" id="review-type">—</p>
                            </div>
                        </div>
                        <div class="review-section">
                            <div class="review-icon"><i class="bi bi-bookmark-fill"></i></div>
                            <div class="review-content">
                                <p class="review-label">Sub-Category</p>
                                <p class="review-value" id="review-subcat">—</p>
                            </div>
                        </div>
                        <div class="review-section">
                            <div class="review-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div class="review-content">
                                <p class="review-label">Location</p>
                                <p class="review-value" id="review-location">—</p>
                            </div>
                        </div>
                        <div class="review-section">
                            <div class="review-icon"><i class="bi bi-calendar-event-fill"></i></div>
                            <div class="review-content">
                                <p class="review-label">Date &amp; Time</p>
                                <p class="review-value" id="review-datetime">—</p>
                            </div>
                        </div>
                        <div class="review-section">
                            <div class="review-icon"><i class="bi bi-file-text-fill"></i></div>
                            <div class="review-content">
                                <p class="review-label">Description</p>
                                <p class="review-value" id="review-description">—</p>
                            </div>
                        </div>
                        <div class="review-section" id="review-actions-row">
                            <div class="review-icon"><i class="bi bi-shield-check"></i></div>
                            <div class="review-content">
                                <p class="review-label">Immediate Actions</p>
                                <p class="review-value" id="review-actions">None specified</p>
                            </div>
                        </div>
                        <div class="review-section" id="review-files-row">
                            <div class="review-icon"><i class="bi bi-paperclip"></i></div>
                            <div class="review-content">
                                <p class="review-label">Supporting Documents</p>
                                <p class="review-value" id="review-files">No files uploaded</p>
                                <div class="review-thumbs" id="review-thumbs"></div>
                            </div>
                        </div>
                    </div>

                    <div class="consent-box">
                        <input type="checkbox" id="consent" name="consent" value="1" required>
                        <label for="consent">
                            <strong>I authorize</strong> the university to share relevant information from this report with the Student Welfare Office and authorized staff members for verification and support purposes. All information will be treated with strict confidentiality.
                        </label>
                    </div>
                </div>

                <div class="wizard-footer">
                    <button type="button" class="btn-wizard btn-wizard-prev" id="btn-prev" disabled>
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <div style="font-size:12px; color:#94a3b8;">
                        Step <span id="current-step">1</span> of 3
                    </div>
                    <button type="button" class="btn-wizard btn-wizard-next" id="btn-next">
                        Next <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-wizard btn-wizard-submit" id="btn-submit" style="display:none;">
                        <i class="bi bi-send-fill"></i> Submit Report
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/crisis-wizard.js') }}"></script>
@endsection
