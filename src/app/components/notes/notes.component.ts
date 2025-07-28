import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Modal } from 'bootstrap';
import { NoteService } from '../../services/note.service';
import { ClasseService } from '../../services/classe.service';
import { MatiereService } from '../../services/matiere.service';
import { EleveService } from '../../services/eleve.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-notes',
  standalone: false,
  templateUrl: './notes.component.html',
  styleUrl: './notes.component.css'
})
export class NotesComponent implements OnInit {
  notes: any[] = [];
  eleves: any[] = [];
  classes: any[] = [];
  matieres: any[] = [];
  periodes: string[] = [];

  selectedClasseId: number | null = null;
  selectedMatiereId: number | null = null;
  selectedNote: any = null;
  selectedEleve: any = null;

  noteModal!: Modal;
  noteForm!: FormGroup;
  isAdmin: boolean = false;
  isEnseignant: boolean = false;

  constructor(
    private fb: FormBuilder,
    private noteService: NoteService,
    private classeService: ClasseService,
    private matiereService: MatiereService,
    private eleveService: EleveService,
    private authService: AuthService
  ) {}

  ngOnInit() {
    this.initForm();
    this.loadInitialData();

   // Initialiser la modale après le chargement du DOM
   const modalElement = document.getElementById('noteModal');
   if (modalElement) {
     this.noteModal = new Modal(modalElement);
   }
  }

  initForm() {
    this.noteForm = this.fb.group({
      periode: ['', Validators.required],
      note: [null, [Validators.required, Validators.min(0), Validators.max(20)]]
    });
  }

  loadInitialData() {
    this.authService.getCurrentUser().subscribe((user: any) => {
      this.isAdmin = user.role === 'admin';
      this.isEnseignant = user.role === 'enseignant';

      // Récupère toutes les matières d'abord
      this.matiereService.getAll().subscribe(matieres => {
        if (this.isAdmin) {
          this.matieres = matieres;
        } else if (this.isEnseignant && user.enseignant?.id) {
          // Ne garder que les matières de l'enseignant connecté
          this.matieres = matieres.filter((m: any) => m.enseignant?.id === user.enseignant.id);
        } else {
          this.matieres = [];
        }

        // En déduire les classes accessibles à partir des matières
        const uniqueClasses = this.matieres
          .map(m => m.classe)
          .filter((classe, index, self) => classe && self.findIndex(c => c.id === classe.id) === index);

        this.classes = uniqueClasses;
        this.selectedClasseId = this.classes.length ? this.classes[0].id : null;

        if (this.selectedClasseId) {
          this.getElevesByClasse();
          this.getMatieresByClasse();
        }
      });

      // Charger les périodes
      this.noteService.getPeriodes().subscribe(p => {
        const anneeActuelle = new Date().getFullYear();
        const anneeScolaire1 = `${anneeActuelle}-${anneeActuelle + 1}`;
        const anneeScolaire2 = `${anneeActuelle + 1}-${anneeActuelle + 2}`;

        this.periodes = p.filter((periode: string) =>
          periode.includes(anneeScolaire1) || periode.includes(anneeScolaire2)
        );
      });
    });
  }


  getNotes() {
    if (!this.selectedClasseId || !this.selectedMatiereId) return;

    this.noteService.getAll().subscribe((notes: any[]) => {
      this.notes = notes.filter(n =>
        this.eleves.some(e => e.id === n.eleve_id) &&
        n.matiere_id === this.selectedMatiereId
      );
    });
  }

  getElevesByClasse() {
    this.eleveService.getAll().subscribe(e => {
      this.eleves = e.filter((el: any) => el.classe_id === this.selectedClasseId);
      this.getNotes();
    });
  }

  getMatieresByClasse() {
    this.matiereService.getAll().subscribe(m => {
      this.matieres = m.filter((m: any) => m.classe_id === this.selectedClasseId);
      this.selectedMatiereId = this.matieres.length ? this.matieres[0].id : null;
    });
  }

  getMatiereName(): string {
    const matiere = this.matieres.find(m => m.id === this.selectedMatiereId);
    return matiere ? matiere.nom : '-';
  }

  openModal(eleve: any) {
    this.selectedEleve = eleve;
    this.selectedNote = this.findNoteForEleve(eleve.id);
    if (this.selectedNote) {
      this.noteForm.patchValue({
        periode: this.selectedNote.periode,
        note: this.selectedNote.note
      });
    } else {
      this.noteForm.reset();
    }
    this.noteModal?.show();
  }

  saveNote() {
    if (!this.noteForm.valid || !this.selectedMatiereId || !this.selectedEleve) return;
    const payload = {
      eleve_id: this.selectedEleve.id,
      matiere_id: this.selectedMatiereId,
      ...this.noteForm.value
    };

    if (this.selectedNote) {
      this.noteService.update(this.selectedNote.id, { note: payload.note, periode: payload.periode }).subscribe(() => this.refresh());
    } else {
      this.noteService.create(payload).subscribe(() => this.refresh());
    }
  }

  refresh() {
    this.getElevesByClasse();
    this.noteForm.reset();
    this.noteModal?.hide(); // Fermer la modale
  }

  onClasseChange() {
    this.getElevesByClasse();
    this.getMatieresByClasse();
  }

  onMatiereChange() {
    this.selectedNote = null;
    this.selectedEleve = null;
    this.noteForm.reset();
    this.getNotes();
  }

  findNoteForEleve(eleveId: number) {
    return this.notes.find(n => n.eleve_id === eleveId && n.matiere_id === this.selectedMatiereId);
  }
}