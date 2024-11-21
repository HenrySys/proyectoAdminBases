import { Component, OnInit } from '@angular/core';
import { User } from '../../../models/user';
import { UserService } from '../../../services/user.service';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Cliente } from '../../../models/cliente';
import { ClienteService } from '../../../services/cliente.service';
import { CommonModule } from '@angular/common';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { timer } from 'rxjs';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [FormsModule,CommonModule, ReactiveFormsModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent implements OnInit {

  userForm!: FormGroup ;

  public user: User;
  public userList: User[];
  public cliente: Cliente;
  public clientList: Cliente[];
  public fileSelected: any;
  public fileCarSelect: any;
  public previsualizacion: string = "";
  public previsualizacionCar: string = "";
  public status: number;





  constructor(
    private UserService: UserService,
    private ClientService: ClienteService,
    private _router: Router,
    private fb: FormBuilder


  ) {
    this.user = new User(1, "", "", "","");
    this.cliente = new Cliente(1,1,"","","","","");
    this.userList = [];
    this.clientList = [];
    this.status = -1;


  }

  ngOnInit(): void {

  }











  initializeForm() {
    this.userForm = this.fb.group({
      usuario: this.fb.group({
        username: ['', Validators.required],
        email: ['', [Validators.required, Validators.email]],
        password_hash: ['', Validators.required]
      }),
      cliente: this.fb.group({
        nombre: ['', Validators.required],
        apellido: ['', Validators.required],
        direccion: ['', Validators.required],
        telefono: ['', Validators.required]
      })
    });
  }

  onSubmit() {
    if (this.userForm.valid) {
      const formData = this.userForm.value;
      console.log('Formulario capturado:', JSON.stringify(formData, null, 2));
      console.log('Datos del formulario de cliente:', formData);

      // Llamada al servicio para enviar los datos del cliente
      this.ClientService.store(formData).subscribe({
        next: (response: any) => {
          console.log('Cliente registrado con éxito:', response);
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'El cliente se ha registrado correctamente.'
          });
          this.userForm.reset(); // Resetear el formulario
          this.changeStatus(0); // Cambiar el estado del formulario (si aplica)

        },
        error: (error) => {
          console.error('Error al registrar el cliente:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al registrar el cliente. Por favor, inténtalo de nuevo.'
          });
          this.changeStatus(2); // Cambiar el estado en caso de error (si aplica)
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Formulario inválido',
        text: 'Por favor, completa todos los campos requeridos.'
      });
    }
  }




  onSubmitLogin(form: any) {
    this.UserService.login(this.user).subscribe({
      next: (response: any) => {
        if (response.status != 401) {
          sessionStorage.setItem("token", response);
          this.UserService.getIdentityFromAPI().subscribe({
            next: (resp: any) => {
              sessionStorage.setItem("identity", JSON.stringify(resp));
              let identity = JSON.parse(sessionStorage.getItem("identity") ?? "{}");
              if (identity.userType == "Admin" || identity.userType == "Empleado") {
                localStorage.clear();
                this._router.navigate(['dashboard']);
              } else {
                this._router.navigate(['menu']);
              }
            },
            error: (error: Error) => {
              console.log(error);
            }
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.'
          });

        }
      },
      error: (error: any) => {
        console.error("Error al iniciar sesión", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al iniciar sesión. Por favor, inténtalo de nuevo.'
        });
      }
    })
  }
  changeStatus(st: number) {
    this.status = st;
    let countdown = timer(3000);
    countdown.subscribe(() => {
      this.status = -1;
    });
  }





}
