<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ZipArchive;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Bulletin;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\BulletinDisponible;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BulletinController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'eleve') {
            $eleve = auth()->user()->eleve;
            $bulletins = Bulletin::with(['eleve.user'])->where('eleve_id', $eleve->id)->get();
            return response()->json($bulletins);
        }

        if (auth()->user()->role === 'admin') {
            return response()->json(Bulletin::with(['eleve.user'])->get());
        }

        return response()->json(['message' => 'Merci de votre intêrêt pour nos données mais nous ne sommes pas intéressés'], 403);
    }

    public function show($id)
    {
        $bulletin = Bulletin::with(['eleve.user'])->findOrFail($id);

        if (auth()->user()->role === 'eleve' && auth()->user()->id !== $bulletin->eleve->user_id) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        if (auth()->user()->role === 'admin' || auth()->user()->role === 'eleve') {
            return response()->json($bulletin);
        }

        return response()->json(['message' => 'Merci de votre intêrêt pour nos données mais nous ne sommes pas intéressés'], 403);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'periode' => 'required'
        ]);

        $eleve = Eleve::with(['user', 'classe.matieres'])->findOrFail($request->eleve_id);

        $notes = Note::where('eleve_id', $eleve->id)
            ->where('periode', $request->periode)
            ->with('matiere.enseignant.user')
            ->get();

        if ($notes->isEmpty()) {
            return response()->json(['message' => 'Aucune note trouvée pour cette période.'], 404);
        }

        if ($notes->count() < $eleve->classe->matieres->count()) {
            return response()->json(['message' => 'Toutes les notes ne sont pas encore disponibles.'], 400);
        }

        // ✅ Calcul moyenne pondérée
        $total = 0;
        $coeffs = 0;

        foreach ($notes as $note) {
            $coeff = $note->matiere->coefficient;
            $total += $note->note * $coeff;
            $coeffs += $coeff;
        }

        $moyenne = round($total / $coeffs, 2);

        $mention = match (true) {
            $moyenne >= 16 => 'Très bien',
            $moyenne >= 14 => 'Bien',
            $moyenne >= 12 => 'Assez bien',
            $moyenne >= 10 => 'Passable',
            default => 'Insuffisant',
        };

        $appreciation = match (true) {
            $moyenne >= 16 => 'Excellent travail',
            $moyenne >= 14 => 'Bon travail',
            $moyenne >= 12 => 'Des efforts à maintenir',
            $moyenne >= 10 => 'Peut mieux faire',
            default => 'Travail insuffisant',
        };

        // ✅ Récupérer tous les élèves de la même classe
        $elevesClasse = Eleve::where('classe_id', $eleve->classe_id)->get();

        $moyennesClasse = [];

        foreach ($elevesClasse as $e) {
            $notesEleve = Note::where('eleve_id', $e->id)
                ->where('periode', $request->periode)
                ->with('matiere')
                ->get();

            if ($notesEleve->count() < $eleve->classe->matieres->count()) {
                continue; // on ignore ceux avec des notes incomplètes
            }

            $totalE = 0;
            $coeffE = 0;

            foreach ($notesEleve as $n) {
                $c = $n->matiere->coefficient;
                $totalE += $n->note * $c;
                $coeffE += $c;
            }

            $moyennesClasse[$e->id] = round($totalE / $coeffE, 2);
        }

        // ✅ Trier les moyennes (décroissant)
        arsort($moyennesClasse);

        // ✅ Déterminer le rang de l’élève
        $rang = array_search($eleve->id, array_keys($moyennesClasse)) + 1;

        // ✅ Génération du PDF
        $pdf = Pdf::loadView('pdf.bulletin', [
            'eleve' => $eleve,
            'notes' => $notes,
            'periode' => $request->periode,
            'moyenne' => $moyenne,
            'mention' => $mention,
            'rang' => $rang,
            'appreciation' => $appreciation
        ]);

        $filename = 'bulletin-' . $eleve->id . $eleve->user->nom . '-' . $eleve->user->prenom . '-'. $eleve->classe->nom . '-' . $request->periode . '-' . now()->timestamp . '.pdf';
        $path = 'bulletins/' . $filename;

        $pdfContent = $pdf->output(); // ✅ 1 seul appel au rendu PDF

        // ✅ Fichier temporaire
        $tempPath = storage_path("app/temp-$filename");
        file_put_contents($tempPath, $pdfContent);

        // ✅ Upload vers S3
        $success = Storage::disk('s3')->putFileAs('bulletins', new File($tempPath), $filename, ['ContentType' => 'application/pdf',]);

        // ✅ Nettoyage du fichier temporaire
        unlink($tempPath);

        Log::info('Bulletin uploadé vers S3', ['success' => $success, 'path' => $path]);
        $pdfUrl = Storage::disk('s3')->url($path);

        // ✅ Supprimer ancien PDF si bulletin déjà existant
        $old = Bulletin::where('eleve_id', $eleve->id)
            ->where('periode', $request->periode)
            ->first();

        if ($old && $old->pdf_path) {
            Storage::disk('s3')->delete(parse_url($old->pdf_path, PHP_URL_PATH));
        }

        // ✅ Créer ou mettre à jour le bulletin
        $bulletin = Bulletin::updateOrCreate(
            [
                'eleve_id' => $eleve->id,
                'periode' => $request->periode
            ],
            [
                'moyenne' => $moyenne,
                'mention' => $mention,
                'appreciation' => $appreciation,
                'pdf_path' => $pdfUrl,
                'rang' => $rang
            ]
        );

        // ✅ Notifier le parent
        if (!empty($eleve->email_parent)) {
            Mail::to($eleve->email_parent)->send(new BulletinDisponible($bulletin));
        }

        return response()->json([
            'message' => 'Bulletin généré avec succès',
            'bulletin' => $bulletin
        ], 201);
    }

    public function downloadZip($periode)
    {
        // Charger eleve, user et classe en une seule requête
        $bulletins = Bulletin::with(['eleve.user', 'eleve.classe'])->where('periode', $periode)->get();

        if ($bulletins->isEmpty()) {
            return response()->json(['message' => 'Aucun bulletin trouvé pour cette période'], 404);
        }

        $periodeSlug = Str::slug($periode);
        $zip = new ZipArchive();
        $filename = "zip/bulletins-{$periodeSlug}-" . Str::random(6) . ".zip";
        $localPath = storage_path("app/{$filename}");

        if (!file_exists(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        if ($zip->open($localPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($bulletins as $b) {
                $path = parse_url($b->pdf_path, PHP_URL_PATH);
                $key = ltrim($path, '/');

                if (!Storage::disk('s3')->exists($key)) {
                    Log::warning("Fichier introuvable dans S3 : $key");
                    continue;
                }

                $content = Storage::disk('s3')->get($key);
                $eleve = $b->eleve->user->prenom . '_' . $b->eleve->user->nom;
                $classe = $b->eleve->classe->nom;
                
                // Nettoyer le nom de classe et élève pour éviter les caractères interdits
                $eleveSlug = $this->slugPreserveCase($eleve);
                $classeSlug = $this->slugPreserveCase($classe);

                // Exemple de nom de fichier : 4e_A_Khadija_Diop.pdf
                $fileName = "{$classeSlug}_{$eleveSlug}_Bulletin_{$periodeSlug}.pdf";

                $zip->addFromString($fileName, $content);
            }

            $zip->close();
        } else {
            return response()->json(['message' => 'Impossible de créer le fichier ZIP'], 500);
        }

        Storage::disk('s3')->put($filename, file_get_contents($localPath));
        $url = Storage::disk('s3')->url($filename);
        unlink($localPath);

        return response()->json(['url' => $url]);
    }

    public function slugPreserveCase($string)
    {
        // Remplace les espaces et tirets par des underscores
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '_', $string);
        // Supprime les underscores multiples
        $slug = preg_replace('/_+/', '_', $slug);
        // Supprime les underscores au début et à la fin
        return trim($slug, '_');
    }

    public function getPeriodes()
    {
        $periodes = Note::select('periode')->distinct()->pluck('periode');
        return response()->json($periodes);
    }

    public function download($id)
    {
        $bulletin = Bulletin::with(['eleve.user'])->findOrFail($id);

        // Sécurité : vérifier que l'utilisateur a le droit d'accès
        $user = auth()->user();

        if ($user->role === 'eleve' && $user->id !== $bulletin->eleve->user_id) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        if ($user->role === 'enseignant') {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        // Extraire le chemin S3 depuis le champ pdf_path
        $path = parse_url($bulletin->pdf_path, PHP_URL_PATH); // ex: /bulletins/bulletin-123.pdf
        $key = ltrim($path, '/'); // enlever le / initial

        if (!Storage::disk('s3')->exists($key)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        $fileName = 'bulletin-' . $bulletin->eleve->user->nom . '-'. $bulletin->eleve->user->prenom . '-' . $bulletin->periode . '.pdf';

        // Générer un lien temporaire valide 5 minutes
        $url = Storage::disk('s3')->temporaryUrl(
            $key,
            now()->addMinutes(5),
            [
                'ResponseContentType' => 'application/pdf',
                'ResponseContentDisposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );

        return response()->json(['url' => $url]);
    }
}