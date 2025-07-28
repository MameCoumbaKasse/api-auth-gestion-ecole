import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ClasseService } from '../../services/classe.service';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-enseignants',
  standalone: false,
  templateUrl: './enseignants.component.html',
  styleUrl: './enseignants.component.css'
})
export class EnseignantsComponent implements OnInit {
  enseignants: any[] = [];
  classes: any[] = [];
  enseignantForm!: FormGroup;
  selectedId: number | null = null;
  selectedClasseId: number | null = null;

  constructor(private fb: FormBuilder, private enseignantService: EnseignantService, private classeService: ClasseService) {}

  ngOnInit() {
    this.initForm();
    this.getEnseignants();
  }

  initForm() {
    this.enseignantForm = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', [Validators.required, Validators.maxLength(155)]],
      email: ['', [Validators.required, Validators.email]],
    });
  }

  getEnseignants() {
    this.enseignantService.getAll().subscribe(data => this.enseignants = data);
  }

  save() {
    if (this.enseignantForm.invalid) return;

    const formData = this.enseignantForm.value;

    if (this.selectedId) {
      this.enseignantService.update(this.selectedId, formData).subscribe(() => {
        this.getEnseignants();
        this.resetForm();
      });
    } else {
      this.enseignantService.create(formData).subscribe(() => {
        this.getEnseignants();
        this.resetForm();
      });
    }
  }

  edit(enseignant: any) {
    this.enseignantForm.patchValue({
      nom: enseignant.user.nom,
      prenom: enseignant.user.prenom,
      email: enseignant.user.email,
    });
    this.selectedId = enseignant.id;
  }

  resetForm() {
    this.enseignantForm.reset();
    this.selectedId = null;
  }
}