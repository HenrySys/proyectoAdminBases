import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Proveedor } from '../../../models/proveedor';
import { ProveedorService } from '../../../services/proveedor.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { timer } from 'rxjs';
import { DomSanitizer } from '@angular/platform-browser';
import Swal from 'sweetalert2';
import { server } from '../../../services/global';


@Component({
  selector: 'app-providers',
  standalone: true,
  imports: [FormsModule, CommonModule],
  templateUrl: './providers.component.html',
  styleUrl: './providers.component.css'
})
export class ProvidersComponent {



  public proveedor: Proveedor;
  private status: number;
  public proveedorLists: Proveedor[];
  public fileSelected: any;
  public previsualizacion: string = "";


  constructor(
    private _proveedorService: ProveedorService,
    private _router: Router,
    private sanitizer: DomSanitizer
    


  ) {
    this.proveedor = new Proveedor(1, "", "", "");
    this.status = -1;
    this.proveedorLists = [];
    this.filteredProveedor = this.proveedorLists;

  }


  ngOnInit(): void {

    this.getProveedores();
    this.visibleData();
    this.pageNumbers();
    // debugger;
  }




  isEditModalOpen: boolean = false;
  provedorToEdit: Proveedor | null = null;

  cancelEdit() {
    this.isEditModalOpen = false;
    this.provedorToEdit = null;
  }

  onEdit(form: any) {
    this.provedorToEdit = form;
    if (this.provedorToEdit) {
      this.isEditModalOpen = true;
      this.proveedor = this.provedorToEdit;
    }

  }

  onsubmitEdit() {
    if (this.provedorToEdit) {
      if (this.proveedor) {
        this._proveedorService.update(this.proveedor).subscribe({
          next: (response: any) => {
            this.getProveedores();
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El proveedor se ha actualizado correctamente.'
            });
          },
          error: (error: any) => {
            console.error("Error al actualizar el proveedor", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al actualizar el proveedor. Por favor, inténtalo de nuevo.'
            });
          }
        });
      }
    } else {
      console.error("No se ha seleccionado ningún proveedor para actualizar");
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se ha seleccionado ningún proveedor para actualizar. Por favor, selecciona un producto.'
      });
    }
  }

  isCrudModalOpen: boolean = false;

  closeCrudModal() {
    this.isCrudModalOpen = false;
  }


  isDeleteModalOpen: boolean = false;
  selectedProveedorId: number | null = null;

  openDeleteConfirmation(proveedorId: number) {
    this.isDeleteModalOpen = true;
    this.selectedProveedorId = proveedorId;
  }

  closeDeleteModal() {
    this.isDeleteModalOpen = false;
    this.selectedProveedorId = null;
  }


  confirmDelete() {
    console.log("ConfirmDelete llamado");

    if (this.selectedProveedorId !== null) {
      console.log(`Eliminando proveedor con ID: ${this.selectedProveedorId}`);

      this._proveedorService.delete(this.selectedProveedorId).subscribe({
        next: (response: any) => {
          console.log("Proveedor eliminado con éxito:", response);
          this.getProveedores();
          // Mostrar el SweetAlert después de que la eliminación sea exitosa
          Swal.fire({
            icon: 'success',
            title: 'Proveedor eliminado',
            showConfirmButton: false,
            timer: 1500
          }).then(() => {
            this.closeDeleteModal();
          });

        },
        error: (error: any) => {
          console.error("Error al eliminar el proveedor", error);

          // Opcional: Mostrar una alerta en caso de error
          Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el proveedor',
            text: 'No se pudo eliminar el proveedor. Inténtalo de nuevo.',
            showConfirmButton: true
          });
        }
      });
    } else {
      console.error("No se ha seleccionado ningún proveedor para eliminar");

      // Opcional: Manejar el caso donde no hay un producto seleccionado
      Swal.fire({
        icon: 'error',
        title: 'Ningún producto seleccionado',
        text: 'Por favor selecciona un proveedor para eliminar.',
        showConfirmButton: true
      });
    }
  }
  isShowModalOpen: boolean = false;

  openShowModal(proveedorId: number) {
    this.isShowModalOpen = true;
    this.selectedProveedorId = proveedorId;
    this._proveedorService.getProveedorById(proveedorId).subscribe({
      next: (response: any) => {
        console.log(response);
        this.proveedor = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener el usuario", error);
      }
    });
  }

  closeShowModal() {
    this.isShowModalOpen = false;
    this.selectedProveedorId = null;
  }

  onSubmit(form: any) {
    // Crear una copia simple del objeto para evitar referencias circulares
    const proveedorPayload = { ...this.proveedor };
  
    this._proveedorService.store(proveedorPayload).subscribe({
      next: (response: any) => {
        if (response.status == 201) {
          form.reset();
          this.changeStatus(0);
          this.getProveedores();
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'El usuario se ha registrado correctamente.',
          });
        } else {
          this.changeStatus(1);
          Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Hubo un problema al registrar el usuario. Por favor, inténtalo de nuevo.',
          });
        }
      },
      error: (error: any) => {
        this.changeStatus(2);
        console.error("Error al registrar el usuario", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al registrar el usuario. Por favor, inténtalo de nuevo.',
        });
      },
    });
  }
  

  getProveedores() {
    this._proveedorService.getProvedores().subscribe({
      next: (response: any) => {
        console.log(response);
        this.proveedorLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredProveedor = this.proveedorLists;
        this.visibleData();
      },
      error: (error: any) => {
        console.error("Error al obtener los proveedores", error);
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
  filteredProveedor: Array<Proveedor> = [];

  visibleData() {
    let start = (this.currentPage - 1) * this.pageSize;
    let end = start + this.pageSize;
    return this.filteredProveedor.slice(start, end);
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
    let totalPage = Math.ceil(this.proveedorLists.length / this.pageSize);
    let pageNumArray = new Array(totalPage);
    return pageNumArray;

  }

  filterData(searchTerm: string) {
    this.filteredProveedor = this.proveedorLists.filter((item) => {
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
