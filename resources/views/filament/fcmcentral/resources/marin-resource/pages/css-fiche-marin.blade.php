<style>
/* Variables de couleurs personnalisées */
:root {
    --success-50: #f0fdf4;
    --success-100: #dcfce7;
    --success-500: #22c55e;
    --success-800: #166534;
   
    --warning-50: #fffbeb;
    --warning-100: #fef3c7;
    --warning-500: #f59e0b;
    --warning-800: #92400e;
   
    --danger-50: #fef2f2;
    --danger-100: #fee2e2;
    --danger-500: #ef4444;
    --danger-800: #991b1b;
}

/* Classes de couleurs personnalisées */
.bg-success-100 { background-color: var(--success-100); }
.bg-success-500 { background-color: var(--success-500); }
.text-success-800 { color: var(--success-800); }
.border-success-500 { border-color: var(--success-500); }

.bg-warning-100 { background-color: var(--warning-100); }
.bg-warning-500 { background-color: var(--warning-500); }
.text-warning-800 { color: var(--warning-800); }
.border-warning-500 { border-color: var(--warning-500); }

.bg-danger-100 { background-color: var(--danger-100); }
.bg-danger-500 { background-color: var(--danger-500); }
.text-danger-800 { color: var(--danger-800); }
.border-danger-500 { border-color: var(--danger-500); }

/* Grilles flexibles */
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
    .grid-sections-4 { grid-template-columns: repeat(2, 1fr); }
    .grid-sections-3 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .grid-sections-4,
    .grid-sections-3,
    .grid-sections-2 {
        grid-template-columns: 1fr;
    }
}

/* Animations pour les sections */
.section-card {
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.section-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Styles pour les badges de pourcentage */
.badge-success {
    background-color: var(--success-100);
    color: var(--success-800);
}

.badge-warning {
    background-color: var(--warning-100);
    color: var(--warning-800);
}

.badge-danger {
    background-color: var(--danger-100);
    color: var(--danger-800);
}

/* Barres de progression améliorées */
.progress-bar {
    transition: width 0.5s ease-in-out;
    border-radius: 9999px;
}

.progress-success { background-color: var(--success-500); }
.progress-warning { background-color: var(--warning-500); }
.progress-danger { background-color: var(--danger-500); }
</style>
