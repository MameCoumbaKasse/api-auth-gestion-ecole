import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ClasseService } from '../../services/classe.service';

@Component({
  selector: 'app-classes',
  standalone: false,
  templateUrl: './classes.component.html',
  styleUrl: './classes.component.css'
})
export class ClassesComponent implements OnInit {
  classes: any[] = [];
  classeForm!: FormGroup;
  selectedId: number | null = null;

  constructor(private fb: FormBuilder, private classeService: ClasseService) {}

  ngOnInit() {
    this.initForm();
    this.getClasses();
    this.autoSelectNiveau();
  }

  initForm() {
    this.classeForm = this.fb.group({
      nom: ['', Validators.required],
      niveau: ['', Validators.required],
    });
  }

  getClasses() {
    this.classeService.getAll().subscribe(data => this.classes = data);
  }

  autoSelectNiveau() {
    const normalise = (val: string) =>
      val.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/gi, "");

    const mapNiveaux: { niveau: string, aliases: string[] }[] = [
      { niveau: '6ème', aliases: ['6e', '6eme', '6'] },
      { niveau: '5ème', aliases: ['5e', '5eme', '5'] },
      { niveau: '4ème', aliases: ['4e', '4eme', '4'] },
      { niveau: '3ème', aliases: ['3e', '3eme', '3'] },
      { niveau: '2nde LE', aliases: ['2le', '2nde le', '2le', '2l'] },
      { niveau: '2nde STT', aliases: ['2stt', '2nde stt'] },
      { niveau: '2nde S', aliases: ['2s', '2nde s'] },
      { niveau: '1ère LE', aliases: ['1le', '1ere le', '1l'] },
      { niveau: '1ère STT', aliases: ['1stt', '1ere stt'] },
      { niveau: '1ère S', aliases: ['1s', '1ere s'] },
      { niveau: 'Terminale A', aliases: ['ta', 'tle a', 'term a', 'terminale a'] },
      { niveau: 'Terminale D', aliases: ['td', 'tle d', 'term d', 'terminale d'] },
      { niveau: 'Terminale C', aliases: ['tc', 'tle c', 'term c', 'terminale c'] },
    ];

    this.classeForm.get('nom')?.valueChanges.subscribe((nom: string) => {
      if (!nom) return;

      const nomNorm = normalise(nom);

      const niveauTrouve = mapNiveaux.find(({ niveau, aliases }) => {
        const allAliases = [niveau, ...aliases];
        return allAliases.some(alias => nomNorm.startsWith(normalise(alias)));
      });

      if (niveauTrouve) {
        this.classeForm.get('niveau')?.setValue(niveauTrouve.niveau);
      }
    });
  }

  save() {
    if (this.classeForm.invalid) return;

    const formData = this.classeForm.value;

    if (this.selectedId) {
      this.classeService.update(this.selectedId, formData).subscribe(() => {
        this.getClasses();
        this.resetForm();
      });
    } else {
      this.classeService.create(formData).subscribe(() => {
        this.getClasses();
        this.resetForm();
      });
    }
  }

  edit(classe: any) {
    this.classeForm.patchValue(classe);
    this.selectedId = classe.id;
  }

  resetForm() {
    this.classeForm.reset();
    this.selectedId = null;
  }
}