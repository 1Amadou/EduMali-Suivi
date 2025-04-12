<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduMali Suivi Pédagogique</title>
    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Styles CSS Internes --}}
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .content-section { display: none; }
        .content-section.active { display: block; }
        th, td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: 600; color: #374151; }
        tbody tr:hover { background-color: #f3f4f6; }
        label { display: block; margin-bottom: 0.25rem; font-weight: 500; color: #374151; font-size: 0.875rem; }
        input[type="text"], input[type="tel"], input[type="date"], input[type="time"], input[type="number"], select, textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; box-shadow: inset 0 1px 2px 0 rgb(0 0 0 / 0.05); font-size: 0.875rem; }
        select:disabled { background-color: #f3f4f6; cursor: not-allowed; }
        textarea { min-height: 4rem; }
        input[readonly] { background-color: #f3f4f6; cursor: default; }
        button { padding: 0.5rem 1rem; background-color: #4f46e5; color: white; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; font-size: 0.875rem;}
        button:hover:not(:disabled) { background-color: #4338ca; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        button.secondary { background-color: #6b7280; } button.secondary:hover:not(:disabled) { background-color: #4b5563; }
        button.danger { background-color: #ef4444; } button.danger:hover:not(:disabled) { background-color: #dc2626; }
        button.success { background-color: #22c55e; } button.success:hover:not(:disabled) { background-color: #16a34a; }
        .timetable { border-collapse: collapse; width: 100%; } .timetable th, .timetable td { border: 1px solid #d1d5db; text-align: center; height: 40px; } .timetable th { background-color: #f3f4f6; font-size: 0.75rem; } .timetable td { cursor: pointer; font-size: 0.7rem; } .timetable td.time-label { font-weight: 500; cursor: default; background-color: #f9fafb; } .timetable td.available { background-color: #e0f2fe; } .timetable td.prep { background-color: #fef9c3; } .timetable td.record { background-color: #dcfce7; } .timetable td.unavailable { background-color: #fecaca; }
        .feuille-route-input { padding: 0.25rem 0.5rem; font-size: 0.8rem; max-width: 120px; } .feuille-route-select { padding: 0.25rem; font-size: 0.8rem; max-width: 130px; } .feuille-route-comment { width: 150px; }
        .alert-message { padding: 0.75rem; margin-bottom: 1rem; border-radius: 0.375rem; font-size: 0.875rem; border: 1px solid transparent;} .alert-success { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; } .alert-error { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; } .alert-info { background-color: #e0f2fe; color: #075985; border-color: #bae6fd; } .alert-warning { background-color: #fef9c3; color: #854d0e; border-color: #fde047; }
        .group:hover .group-hover\:opacity-100 { opacity: 1; } .group\/matiere:hover .group-hover\/matiere\:opacity-100 { opacity: 1; } .group\/chapitre:hover .group-hover\/chapitre\:opacity-100 { opacity: 1; } .group\/lecon:hover .group-hover\/lecon\:opacity-100 { opacity: 1; }
        .crud-buttons button { color: white !important; border-radius: 0.25rem; padding: 0.125rem 0.375rem; font-size: 0.7rem; line-height: 1; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .crud-buttons button:hover { filter: brightness(110%); }
        .no-print { display: initial; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    {{-- ======================= Barre Latérale ======================= --}}
    <aside class="w-64 bg-indigo-800 text-indigo-100 p-4 flex flex-col shadow-lg flex-shrink-0">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">EduMali Suivi</h1>
        <nav class="flex flex-col space-y-1">
            <a href="#accueil" class="nav-link p-2 rounded hover:bg-indigo-700 active" data-section="accueil">Accueil</a>
            <a href="#enseignants" class="nav-link p-2 rounded hover:bg-indigo-700" data-section="enseignants">Enseignants</a>
            <a href="#horaires" class="nav-link p-2 rounded hover:bg-indigo-700" data-section="horaires">Emplois du Temps</a>
            <a href="#classes" class="nav-link p-2 rounded hover:bg-indigo-700" data-section="classes">Structure Pédago.</a>
            <a href="#planification" class="nav-link p-2 rounded hover:bg-indigo-700" data-section="planification">Planification Studio</a>
            <a href="#feuille-route" class="nav-link p-2 rounded hover:bg-indigo-700" data-section="feuille-route">Feuille de Route</a>
        </nav>
        <div class="mt-auto text-xs text-indigo-300 text-center">
            Version 6.0 (Laravel)
        </div>
    </aside>

    {{-- ======================= Contenu Principal ======================= --}}
    <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-md min-h-full">

             {{-- Conteneur Alertes Globales --}}
             <div id="global-alert-container" class="mb-4 sticky top-0 z-50 -mx-6 -mt-6 p-4 bg-opacity-90"></div>

            {{-- ======================= SECTION ACCUEIL ======================= --}}
            <section id="accueil" class="content-section active">
                 <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tableau de Bord</h2>
                 <p class="text-gray-600 mb-4">Vue d'ensemble de l'avancement et de la planification.</p>
                 <div id="dashboard-content" class="mt-6 space-y-6">
                    <p id="dashboard-loading" class="text-gray-500 italic">Chargement...</p>
                    <div id="dashboard-stats-container" style="display: none;"> <h3 class="text-xl font-semibold text-gray-700 mb-3">Statistiques</h3> <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"> <div class="p-4 bg-blue-100 rounded-lg shadow stats-card"><h4 class="font-semibold text-blue-800">Classes</h4><p id="db-stat-classes" class="text-3xl font-bold text-blue-900">...</p></div> <div class="p-4 bg-indigo-100 rounded-lg shadow stats-card"><h4 class="font-semibold text-indigo-800">Matières</h4><p id="db-stat-matieres" class="text-3xl font-bold text-indigo-900">...</p></div> <div class="p-4 bg-purple-100 rounded-lg shadow stats-card"><h4 class="font-semibold text-purple-800">Chapitres</h4><p id="db-stat-chapitres" class="text-3xl font-bold text-purple-900">...</p></div> <div class="p-4 bg-pink-100 rounded-lg shadow stats-card"><h4 class="font-semibold text-pink-800">Leçons</h4><p id="db-stat-lecons" class="text-3xl font-bold text-pink-900">...</p></div> </div> </div>
                    <div id="dashboard-lecons-container" style="display: none;"> <h3 class="text-xl font-semibold text-gray-700 mb-3">Avancement Leçons</h3> <div id="db-lecon-status" class="flex flex-wrap gap-3 items-center"></div> </div>
                     <div id="dashboard-planning-container" style="display: none;"> <h3 class="text-xl font-semibold text-gray-700 mb-3">Planning Studio (7 Prochains Jours)</h3> <div id="db-upcoming-planning" class="text-sm space-y-2 border rounded-lg p-3 bg-gray-50 max-h-60 overflow-y-auto"></div> </div>
                 </div>
            </section>

            {{-- ======================= SECTION ENSEIGNANTS ======================= --}}
            <section id="enseignants" class="content-section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Gestion des Enseignants</h2>
                {{-- Formulaire Enseignant (MAJ: Avec Select Matière Principale) --}}
                <div class="mb-8 p-4 border rounded-lg bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4" id="teacher-form-title">Ajouter / Modifier Enseignant</h3>
                    <form id="teacher-form" class="space-y-4">
                        <input type="hidden" id="teacher-id" name="teacher-id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label for="nom">Nom:</label><input type="text" id="nom" name="nom" required></div>
                            <div><label for="telephone">Téléphone:</label><input type="tel" id="telephone" name="telephone"></div>
                        </div>
                        {{-- Select Matière Principale --}}
                        <div>
                            <label for="teacher-matiere-principale">Matière Principale Assignée:</label>
                            <select id="teacher-matiere-principale" name="matiere_principale_id" required class="mt-1 block w-full">
                                <option value="">-- Charger les matières... --</option>
                                {{-- Sera rempli par JS --}}
                            </select>
                             <p class="text-xs text-gray-500 mt-1">Assignez la matière principale dont cet enseignant est responsable.</p>
                        </div>
                        {{-- Commentaires --}}
                        <div><label for="commentaires">Commentaires:</label><textarea id="commentaires" name="commentaires" rows="2"></textarea></div>
                        <div class="flex space-x-3">
                            <button type="submit" id="save-teacher-btn">Enregistrer</button>
                            <button type="button" id="cancel-teacher-btn" class="secondary" style="display: none;">Annuler</button>
                        </div>
                    </form>
                </div>
                {{-- Liste des Enseignants (MAJ: sans colonne matières) --}}
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Liste des Enseignants</h3>
                <div class="overflow-x-auto border rounded-lg mb-8">
                    <table class="min-w-full w-full table-auto">
                        <thead>
                            <tr>
                                <th class="w-2/5 px-4 py-2">Nom</th>
                                <th class="w-1/5 px-4 py-2">Téléphone</th>
                                <th class="w-2/5 px-4 py-2">Commentaires</th>
                                <th class="w-auto px-4 py-2 text-right">Actions</th> {{-- Actions alignées à droite --}}
                            </tr>
                        </thead>
                        <tbody id="teacher-list" class="bg-white">
                            {{-- Rempli par JS via API --}}
                        </tbody>
                    </table>
                </div>
            </section>

             <section id="horaires" class="content-section">
                 <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestion des Emplois du Temps<span id="edt-save-indicator" class="text-sm text-gray-500 ml-2 font-normal"></span></h2>
                 <p class="text-gray-600 mb-6 text-sm">Sélectionnez un enseignant pour visualiser et modifier sa disponibilité hebdomadaire. Enregistrement automatique.</p>
                 <div class="mb-4 flex flex-wrap items-center gap-4">
                    <label for="select-teacher-schedule" class="mr-2 font-medium shrink-0">Choisir un enseignant:</label>
                    <select id="select-teacher-schedule" class="p-1 border rounded w-auto max-w-xs shrink-0"></select>
                    {{-- Boutons Reset et Imprimer --}}
                    <div class="flex-grow flex justify-end space-x-2">
                         <button id="reset-timetable-btn" class="danger text-sm py-1 px-2 no-print shrink-0" style="display: none;">Réinitialiser EDT</button>
                         <button id="print-timetable-btn" class="secondary text-sm py-1 px-2 no-print shrink-0" style="display: none;">Imprimer Emploi du Temps</button>
                    </div>
                 </div>
                 <div id="teacher-timetable-container" class="border rounded-lg p-4 bg-gray-50 min-h-[300px]">
                     <p class="text-gray-500 italic text-center p-4">Sélectionnez un enseignant ci-dessus.</p>
                 </div>
                 <div class="mt-2 text-xs flex flex-wrap gap-x-4 gap-y-1">
                     <span>Légende:</span>
                     <span class="px-2 py-0.5 rounded bg-sky-100 border border-sky-300">Disponible</span>
                     <span class="px-2 py-0.5 rounded bg-yellow-100 border border-yellow-300">Préparation</span>
                     <span class="px-2 py-0.5 rounded bg-green-100 border border-green-300">Enregistrement</span>
                     <span class="px-2 py-0.5 rounded bg-red-200 border border-red-300">Non Disponible</span>
                 </div>
             </section>

            {{-- ======================= SECTION STRUCTURE (CLASSES...) ======================= --}}
            <section id="classes" class="content-section">
                 <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestion de la Structure Pédagogique</h2>
                 <p class="text-gray-600 mb-4 text-sm">
                    Gérez classes, matières (et leur responsable), chapitres et leçons via les boutons (<span class="inline-block px-1 py-0.5 text-xs bg-green-600 text-white rounded">➕</span>,<span class="inline-block px-1 py-0.5 text-xs bg-yellow-500 text-white rounded">✏️</span>,<span class="inline-block px-1 py-0.5 text-xs bg-red-600 text-white rounded">🗑️</span>).
                 </p>
                 <div id="classes-structure-container" class="min-h-[200px]">
                     <p class="text-gray-500 italic p-4">Chargement...</p>
                 </div>
            </section>

            {{-- ======================= SECTION PLANIFICATION STUDIO ======================= --}}
            <section id="planification" class="content-section">
                 <h2 class="text-2xl font-semibold text-gray-800 mb-6">Planification des Enregistrements Studio</h2>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     {{-- Formulaire de planification (Revu) --}}
                     <div class="p-4 border rounded-lg bg-gray-50">
                         <h3 class="text-xl font-semibold text-gray-700 mb-4">Planifier une Session</h3>
                         <form id="planning-form" class="space-y-3">
                             <div> <label for="plan-teacher">Enseignant:</label> <select id="plan-teacher" name="enseignant_id" required></select> </div>
                             <div> <label for="plan-lesson-select">Leçon à planifier:</label> <select id="plan-lesson-select" name="lecon_id" required class="mt-1 block w-full disabled:bg-gray-100" disabled> <option value="">-- Sélectionnez enseignant --</option> </select> <div id="plan-lesson-details" class="text-xs text-gray-600 mt-1 h-4"></div> </div>
                             <div> <label for="plan-lesson-desc">Description Planning (auto):</label> <input type="text" id="plan-lesson-desc" name="lecon_description" class="text-sm bg-gray-100 border-gray-200" readonly placeholder="Détail leçon..."> </div>
                             <div class="grid grid-cols-2 gap-3"> <div><label for="plan-date">Date:</label><input type="date" id="plan-date" name="date_reservation" required></div> <div><label for="plan-studio">Studio:</label><select id="plan-studio" name="studio_id" required><option value="1">Studio 1</option><option value="2">Studio 2</option></select></div> </div>
                             <div class="grid grid-cols-2 gap-3"> <div><label for="plan-start-time">Début:</label><input type="time" id="plan-start-time" name="heure_debut" step="1800" required></div> <div><label for="plan-end-time">Fin:</label><input type="time" id="plan-end-time" name="heure_fin" step="1800" required></div> </div>
                             <button type="submit">Vérifier & Planifier</button>
                         </form>
                     </div>
                     {{-- Vue du planning --}}
                     <div class="p-4 border rounded-lg">
                          <h3 class="text-xl font-semibold text-gray-700 mb-4">Planning des Studios</h3>
                          <div class="mb-2"> <label for="view-planning-date" class="mr-2">Voir planning du:</label> <input type="date" id="view-planning-date" class="p-1 border rounded w-auto"> </div>
                          <div id="studio-planning-view" class="space-y-3 min-h-[200px]"> <p class="text-sm italic p-4">Sélectionnez date.</p> </div>
                     </div>
                 </div>
            </section>

            {{-- ======================= SECTION FEUILLE DE ROUTE ======================= --}}
            <section id="feuille-route" class="content-section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Feuille de Route (Suivi des Leçons)</h2>
                <div class="mb-6 flex flex-wrap gap-4 items-end">
                     <div><label for="select-classe-route">Classe:</label><select id="select-classe-route" name="select-classe-route" class="w-auto md:w-64 p-1 border rounded"></select></div>
                     <button id="generate-route-btn" class="secondary text-sm py-1 px-2">Afficher</button>
                     <button id="generate-blank-route-btn" class="secondary text-sm py-1 px-2">Vierge</button>
                     <button id="save-route-changes-btn" class="success text-sm py-1 px-2" style="display: none;">Enregistrer Modifs</button>
                     <button id="print-route-btn" class="secondary ml-auto text-sm py-1 px-2 no-print">Imprimer</button>
                </div>
                <div id="feuille-route-content" class="border rounded-lg overflow-hidden min-h-[200px]">
                    <p class="p-4 text-center text-gray-500">Sélectionnez une classe.</p>
                </div>
            </section>

        </div> {{-- Fin max-w-7xl --}}
    </main>

    {{-- Inclusion Fichier JavaScript --}}
    <script src="{{ asset('js/app.js') }}"></script>

</body>
</html>