import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BulletinsComponent } from './bulletins.component';

describe('BulletinsComponent', () => {
  let component: BulletinsComponent;
  let fixture: ComponentFixture<BulletinsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [BulletinsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BulletinsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
