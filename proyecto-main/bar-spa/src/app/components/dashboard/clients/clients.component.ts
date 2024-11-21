import { Component, OnInit} from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ReactiveFormsModule } from '@angular/forms';
import { Cliente } from '../../../models/cliente';
import { ClienteService } from '../../../services/cliente.service';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { timer } from 'rxjs';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-clients',
  standalone: true,
  imports: [ReactiveFormsModule,FormsModule, CommonModule],
  templateUrl: './clients.component.html',
  styleUrl: './clients.component.css'
})
export class ClientsComponent implements OnInit {

  userForm!: FormGroup ;
  public cliente : Cliente;
  public clienteLists: Cliente [];
  public status: number;

  constructor(
    private fb: FormBuilder,
    private ClienteService: ClienteService
  ){
    this.cliente = new Cliente(1,1,"","","","","");
    this.status = -1;
    this.clienteLists = [];
    this.filteredCliente = this.clienteLists;
  }

  ngOnInit() {
    this.initializeForm();
    this.getClientes();
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
      this.ClienteService.store(formData).subscribe({
        next: (response: any) => {
          console.log('Cliente registrado con éxito:', response);
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'El cliente se ha registrado correctamente.'
          });
          this.userForm.reset(); // Resetear el formulario
          this.changeStatus(0); // Cambiar el estado del formulario (si aplica)
          this.getClientes(); // Actualizar la lista de clientes (si tienes un método para eso)
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
  

  isEditModalOpen: boolean = false;
  ClienteToEdit: Cliente | null = null;

  cancelEdit() {
    this.isEditModalOpen = false;
    this.ClienteToEdit = null;
  }

  onEdit(form: any) {
    this.ClienteToEdit = form;
    if (this.ClienteToEdit) {
      this.isEditModalOpen = true;
      this.cliente = this.ClienteToEdit;
    }

  }

  onsubmitEdit() {
    if (this.ClienteToEdit) {
      if (this.cliente) {
        this.ClienteService.updateCliente(this.cliente).subscribe({
          next: (response: any) => {
            this.getClientes();
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El cliente se ha actualizado correctamente.'
            });
          },
          error: (error: any) => {
            console.error("Error al actualizar el cliente", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al actualizar el cliente. Por favor, inténtalo de nuevo.'
            });
          }
        });
      }
    } else {
      console.error("No se ha seleccionado ningún cliente para actualizar");
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se ha seleccionado ningún cliente para actualizar. Por favor, selecciona un cliente.'
      });
    }
  }

  isCrudModalOpen: boolean = false;

  closeCrudModal() {
    this.isCrudModalOpen = false;
  }


  isDeleteModalOpen: boolean = false;
  selectedClientId: number | null = null;

  openDeleteConfirmation(userId: number) {
    this.isDeleteModalOpen = true;
    this.selectedClientId = userId;
  }

  closeDeleteModal() {
    this.isDeleteModalOpen = false;
    this.selectedClientId = null;
  }


  confirmDelete() {
    console.log("ConfirmDelete llamado");

    if (this.selectedClientId !== null) {
      console.log(`Eliminando cliente con ID: ${this.selectedClientId}`);

      this.ClienteService.delete(this.selectedClientId).subscribe({
        next: (response: any) => {
          console.log("Producto cliente con éxito:", response);
          this.getClientes();
          // Mostrar el SweetAlert después de que la eliminación sea exitosa
          Swal.fire({
            icon: 'success',
            title: 'cliente eliminado',
            showConfirmButton: false,
            timer: 1500
          }).then(() => {
            this.closeDeleteModal();
          });

        },
        error: (error: any) => {
          console.error("Error al eliminar el cliente", error);

          // Opcional: Mostrar una alerta en caso de error
          Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el cliente',
            text: 'No se pudo eliminar el cliente. Inténtalo de nuevo.',
            showConfirmButton: true
          });
        }
      });
    } else {
      console.error("No se ha seleccionado ningún cliente para eliminar");

      // Opcional: Manejar el caso donde no hay un producto seleccionado
      Swal.fire({
        icon: 'error',
        title: 'Ningún cliente seleccionado',
        text: 'Por favor selecciona un cliente para eliminar.',
        showConfirmButton: true
      });
    }
  }
  isShowModalOpen: boolean = false;

  openShowModal(clienteId: number) {
    this.isShowModalOpen = true;
    this.selectedClientId = clienteId;
    this.ClienteService.getCliente(clienteId).subscribe({
      next: (response: any) => {
        console.log(response);
        this.cliente = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener el cliente", error);
      }
    });
  }

  closeShowModal() {
    this.isShowModalOpen = false;
    this.selectedClientId = null;
  }
  getClientes() {
    this.ClienteService.getClientes().subscribe({
      next: (response: any) => {
        console.log(response);
        this.clienteLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredCliente = this.clienteLists;
        this.visibleData();
      },
      error: (error: any) => {
        console.error("Error al obtener los Cliente", error);
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
  filteredCliente: Array<Cliente> = [];

  visibleData() {
    let start = (this.currentPage - 1) * this.pageSize;
    let end = start + this.pageSize;
    return this.filteredCliente.slice(start, end);
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
    let totalPage = Math.ceil(this.clienteLists.length / this.pageSize);
    let pageNumArray = new Array(totalPage);
    return pageNumArray;

  }

  filterData(searchTerm: string) {
    this.filteredCliente = this.clienteLists.filter((item) => {
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
