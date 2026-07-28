<style>
    .grid-sections-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .grid-sections-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .grid-sections-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .grid-sections-2,
        .grid-sections-3,
        .grid-sections-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .grid-sections-2,
        .grid-sections-3,
        .grid-sections-4 {
            grid-template-columns: 1fr;
        }
    }

    .section-card {
        transition: all 0.3s ease;
    }

    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* ✅ DARK MODE COMPATIBLE */
    .dark .section-card:hover {
        box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.1), 0 2px 4px -1px rgba(255, 255, 255, 0.06);
    }

    .progress-bar {
        transition: width 0.5s ease-in-out;
    }

    .progress-success {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .progress-warning {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .progress-danger {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .progress-gray {
        background: linear-gradient(90deg, #9ca3af 0%, #6b7280 100%);
    }

    .note-card {
        transition: all 0.2s ease;
    }

    .note-card:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* ✅ DARK MODE POUR NOTE CARD */
    .dark .note-card:hover {
        box-shadow: 0 2px 4px rgba(255, 255, 255, 0.1);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* ✅ DARK MODE SCROLLBAR */
    .dark .custom-scrollbar::-webkit-scrollbar-track {
        background: #1e293b;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* ✅ AMÉLIORATION PROGRESSION SAVOIR-FAIRE EN DARK MODE */
    .sf-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .dark .sf-item {
        background: #1e293b;
        border-color: #334155;
    }

    .sf-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .dark .sf-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        background: #334155;
    }

    .sf-group-header {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .dark .sf-group-header {
        background: #0f172a;
        border-color: #1e293b;
    }

    /* ✅ AMÉLIORATION BARRE DE PROGRESSION */
    .progress-bg {
        background: #e5e7eb;
    }

    .dark .progress-bg {
        background: #374151;
    }

    /* ✅ TEXTES LISIBLES EN DARK MODE */
    .text-adaptive-gray {
        color: #4b5563;
    }

    .dark .text-adaptive-gray {
        color: #9ca3af;
    }

    .text-adaptive-dark {
        color: #111827;
    }

    .dark .text-adaptive-dark {
        color: #f9fafb;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .section-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    /* Blanc */
    .text-white { color: #rgb(255, 255, 255) }
    .text-dark { color: #rgb(0, 0, 0) }
    .text-gray { color:#94a3b8;) }

    /* Vert */
.text-green-600 { color: #16a34a; }
.text-green-800 { color: #166534; }
.text-green-400 { color :#6be6bd;}
.bg-green-50 { background-color: #f0fdf4; }
.bg-green-100 { background-color: #dcfce7; }
.border-green-200 { border-color: #bbf7d0; }
.dark .text-green-600, .dark .text-green-800 { color: #bbf7d0; }
.dark .bg-green-50, .dark .bg-green-100 { background-color: #052e16; }
.dark .border-green-200 { border-color: #166534; }

/* Bleu */
.text-blue-600 { color: #2563eb; }
.text-blue-800 { color: #1e40af; }
.text-blue-400 { color: #4065df; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-blue-100 { background-color: #dbeafe; }
.border-blue-200 { border-color: #bfdbfe; }
.dark .text-blue-600, .dark .text-blue-800 { color: #bfdbfe; }
.dark .bg-blue-50, .dark .bg-blue-100 { background-color: #1e293b; }
.dark .border-blue-200 { border-color: #1e40af; }

/* Violet */
.text-purple-600 { color: #9333ea; }
.text-purple-800 { color: #581c87; }
.text-purple-400 { color: #8f43ca; }
.bg-purple-50 { background-color: #faf5ff; }
.bg-purple-100 { background-color: #f3e8ff; }
.border-purple-200 { border-color: #e9d5ff; }
.dark .text-purple-600, .dark .text-purple-900 { color: #e9d5ff; }
.dark .bg-purple-50, .dark .bg-purple-100 { background-color: #2e1065; }
.dark .border-purple-200 { border-color: #7c3aed; }

/* Orange */
.text-orange-600 { color: #ea580c; }
.text-orange-800 { color: #bb4b0f; }
.text-orange-400 { color: #f0844a; }
.bg-orange-50 { background-color: #fff7ed; }
.bg-orange-100 { background-color: #ffedd5; }
.border-orange-200 { border-color: #fed7aa; }
.dark .text-orange-600 { color: #fed7aa; }
.dark .bg-orange-50, .dark .bg-orange-100 { background-color: #7c2d12; }
.dark .border-orange-200 { border-color: #ea580c; }

/* Rouge */
.text-red-600 { color: #dc2626; }
.bg-red-100 { background-color: #fee2e2; }
.border-red-500 { border-color: #ef4444; }
.dark .text-red-600 { color: #fee2e2; }
.dark .bg-red-100 { background-color: #7f1d1d; }
.dark .border-red-500 { border-color: #fee2e2; }

/* Bleu */
.bg-gradient-blue {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}
.dark .bg-gradient-blue {
    background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
}

/* Vert */
.bg-gradient-green {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}
.dark .bg-gradient-green {
    background: linear-gradient(135deg, #052e16 0%, #22c55e 100%);
}

/* Violet */
.bg-gradient-purple {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
}
.dark .bg-gradient-purple {
    background: linear-gradient(135deg, #2e1065 0%, #a78bfa 100%);
}

/* Orange */
.bg-gradient-orange {
    background: linear-gradient(135deg, #f1eacb 0%, #fa8211 100%);
}
.dark .bg-gradient-orange {
    background: linear-gradient(135deg, #654010 0%, #f77d0b 100%);
}

.text-5xl{
    font-size: 40px;
}

.text-3xl{
    font-size: 30px;
}

</style>
