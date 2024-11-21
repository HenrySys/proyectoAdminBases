import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { EmpleadoService } from '../../../services/empleado.service';
import { Empleado } from '../../../models/empleado';
import Swal from 'sweetalert2';
import { timer } from 'rxjs';
import { ReactiveFormsModule } from '@angular/forms';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-employees',
  standalone: true,
  imports: [ReactiveFormsModule,FormsModule, CommonModule],
  templateUrl: './employees.component.html',
  styleUrl: './employees.component.css'
})
export class EmployeesComponent {

  userForm!: FormGroup ;
  public empleado: Empleado;
  private status: number;
  public empleadoLists: Empleado[];
  public fileSelected: any;
  public previsualizacion: string = "";


  constructor(
    private fb: FormBuilder,
    private EmpleadoService: EmpleadoService
  ){
    this.empleado = new Empleado(1,1,"","","","");
    this.status = -1;
    this.empleadoLists = [];
    this.filteredEmpleado = this.empleadoLists;
  }

  ngOnInit() {
    this.initializeForm();
    this.getEmployees();
    this.visibleData();
    this.pageNumbers();
  }

  initializeForm() {
    this.userForm = this.fb.group({
      usuario: this.fb.group({
        username: ['', Validators.required],
        email: ['', [Validators.required, Validators.email]],
        password_hash: ['', Validators.required]
      }),
      empleado: this.fb.group({
        nombre: ['', Validators.required],
        apellido: ['', Validators.required],
        telefono: ['', Validators.required]
      })
    });
  }

  onSubmit() {
    if (this.userForm.valid) {
      const formData = this.userForm.value;

      // Crear una copia simple del objeto para evitar referencias circulares
      const userPayload = { ...formData };
      console.log(userPayload);

      this.EmpleadoService.store(userPayload).subscribe({
        next: (response: any) => {
          if (response.status === 201) {
            this.userForm.reset(); // Restablecer el formulario
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El usuario se ha registrado correctamente.',
            });
          } else {
            Swal.fire({
              icon: 'warning',
              title: 'Advertencia',
              text: 'Hubo un problema al registrar el usuario. Por favor, inténtalo de nuevo.',
            });
          }
        },
        error: (error: any) => {
          console.error('Error al registrar el usuario', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al registrar el usuario. Por favor, inténtalo de nuevo.',
          });
        },
      });
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Formulario incompleto',
        text: 'Por favor, completa todos los campos obligatorios antes de enviar.',
      });
    }
  }

  isEditModalOpen: boolean = false;
  empleadoToEdit: Empleado | null = null;

  cancelEdit() {
    this.isEditModalOpen = false;
    this.empleadoToEdit = null;
  }

  onEdit(form: any) {
    this.empleadoToEdit = form;
    if (this.empleadoToEdit) {
      this.isEditModalOpen = true;
      this.empleado = this.empleadoToEdit;
    }

  }

  onsubmitEdit() {
    if (this.empleadoToEdit) {
      if (this.empleado) {
        this.EmpleadoService.update(this.empleado).subscribe({
          next: (response: any) => {
            this.getEmployees();
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El empleado se ha actualizado correctamente.'
            });
          },
          error: (error: any) => {
            console.error("Error al actualizar el usuario", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al actualizar el empleado. Por favor, inténtalo de nuevo.'
            });
          }
        });
      }
    } else {
      console.error("No se ha seleccionado ningún usuario para actualizar");
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se ha seleccionado ningún empleado para actualizar. Por favor, selecciona un producto.'
      });
    }
  }

  isCrudModalOpen: boolean = false;

  closeCrudModal() {
    this.isCrudModalOpen = false;
  }


  isDeleteModalOpen: boolean = false;
  selectedEmpleadoId: number | null = null;

  openDeleteConfirmation(userId: number) {
    this.isDeleteModalOpen = true;
    this.selectedEmpleadoId = userId;
  }

  closeDeleteModal() {
    this.isDeleteModalOpen = false;
    this.selectedEmpleadoId = null;
  }


  confirmDelete() {
    console.log("ConfirmDelete llamado");

    if (this.selectedEmpleadoId !== null) {
      console.log(`Eliminando usuario con ID: ${this.selectedEmpleadoId}`);

      this.EmpleadoService.delete(this.selectedEmpleadoId).subscribe({
        next: (response: any) => {
          console.log("Producto empleado con éxito:", response);
          this.getEmployees();
          // Mostrar el SweetAlert después de que la eliminación sea exitosa
          Swal.fire({
            icon: 'success',
            title: 'empleado eliminado',
            showConfirmButton: false,
            timer: 1500
          }).then(() => {
            this.closeDeleteModal();
          });

        },
        error: (error: any) => {
          console.error("Error al eliminar el usuario", error);

          // Opcional: Mostrar una alerta en caso de error
          Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el empleado',
            text: 'No se pudo eliminar el empleado. Inténtalo de nuevo.',
            showConfirmButton: true
          });
        }
      });
    } else {
      console.error("No se ha seleccionado ningún empleado para eliminar");

      // Opcional: Manejar el caso donde no hay un producto seleccionado
      Swal.fire({
        icon: 'error',
        title: 'Ningún empleado seleccionado',
        text: 'Por favor selecciona un empleado para eliminar.',
        showConfirmButton: true
      });
    }
  }

  isShowModalOpen: boolean = false;

  openShowModal(userId: number) {
    this.isShowModalOpen = true;
    this.selectedEmpleadoId = userId;
    this.EmpleadoService.getEmpleado(userId).subscribe({
      next: (response: any) => {
        console.log(response);
        this.empleado = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener el empleado", error);
      }
    });
  }

  closeShowModal() {
    this.isShowModalOpen = false;
    this.selectedEmpleadoId = null;
  }

  getEmployees() {
    this.EmpleadoService.getEmpleados().subscribe({
      next: (response: any) => {
        console.log(response);
        this.empleadoLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredEmpleado = this.empleadoLists;
        this.visibleData();
      },
      error: (error: any) => {
        console.error("Error al obtener los Empleados", error);
      }
    });
  };

  changeStatus(st: number) {
    this.status = st;
    let countdown = timer(3000);
    countdown.subscribe(() => {
      this.status = -1;
    });
  }
  isDropdownOpen: boolean[] = [];

  toggleDropdown(index: number) {
    this.isDropdownOpen[index] = !this.isDropdownOpen[index];
  }

  currentPage: number = 1;
  pageSize: number = 10;
  filteredEmpleado: Array<Empleado> = [];

  visibleData() {
    let start = (this.currentPage - 1) * this.pageSize;
    let end = start + this.pageSize;
    return this.filteredEmpleado.slice(start, end);
  }

  nextPage() {
    this.currentPage++;
    this.visibleData();
  }

  previousPage() {
    this.currentPage--;
    this.visibleData();
  }

  pageNumbers() {
    let totalPage = Math.ceil(this.empleadoLists.length / this.pageSize);
    let pageNumArray = new Array(totalPage);
    return pageNumArray;

  }

  filterData(searchTerm: string) {
    this.filteredEmpleado = this.empleadoLists.filter((item) => {
      return Object.values(item).some((value) => {
        return typeof value === 'string' && value.toLowerCase().includes(searchTerm.toLowerCase());
      });
    });

  }

  goToPage(page: number) {
    this.currentPage = page;
    this.visibleData();
  }


}


