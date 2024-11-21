import { Component, OnInit } from '@angular/core';

import Swal from 'sweetalert2';
import { timer } from 'rxjs';
import { ReactiveFormsModule } from '@angular/forms';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Inventario } from '../../../models/inventario';
import { InventarioService } from '../../../services/inventario.service';
import { ProductoService } from '../../../services/producto.service';
import { Producto } from '../../../models/producto';
import { server } from '../../../services/global';




@Component({
  selector: 'app-inventario',
  standalone: true,
  imports: [FormsModule, CommonModule],
  templateUrl: './inventario.component.html',
  styleUrl: './inventario.component.css'
})

export class InventarioComponent implements OnInit {

  public invemtario: Inventario;
  private status: number;
  public inventarioLists: Inventario[];
  public producto: Producto;
  public fileSelected: any;
  public previsualizacion: string = "";
  public imageUrl: any = "";
  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";
  public cantidad: number = 0;

  constructor(
    private _InventarioService: InventarioService,
    private _ProductoService: ProductoService
  ){
    this.invemtario = new Inventario(1,1,1,"");
    this.producto = new Producto(1,1,1,"","",1,"");
    this.status = -1;
    this.inventarioLists = [];
    this.filteredInventario = this.inventarioLists;

  }


  ngOnInit() {
    this.getInventarios();
    this.visibleData();
    this.pageNumbers();
  }

  getInventarios() {
    this._InventarioService.getInventarios().subscribe({
      next: (response: any) => {
        console.log(response);
        this.inventarioLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredInventario = this.inventarioLists;
        this.visibleData();
      },
      error: (error: any) => {
        console.error("Error al obtener el inventario", error);
      }
    });
  };

  currentPage: number = 1;
  pageSize: number = 10;
  filteredInventario: Array<Inventario> = [];

  visibleData() {
    let start = (this.currentPage - 1) * this.pageSize;
    let end = start + this.pageSize;
    return this.filteredInventario.slice(start, end);
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
    let totalPage = Math.ceil(this.inventarioLists.length / this.pageSize);
    let pageNumArray = new Array(totalPage);
    return pageNumArray;

  }

  filterData(searchTerm: string) {
    this.filteredInventario = this.inventarioLists.filter((item) => {
      return Object.values(item).some((value) => {
        return typeof value === 'string' && value.toLowerCase().includes(searchTerm.toLowerCase());
      });
    });

  }

  goToPage(page: number) {
    this.currentPage = page;
    this.visibleData();
  }

  isDropdownOpen: boolean[] = [];

  toggleDropdown(index: number) {
    this.isDropdownOpen[index] = !this.isDropdownOpen[index];
  }


  isShowModalOpen: boolean = false;
  selectedProductId: number | null = null;


  openShowModal(productId: number) {
    this.isShowModalOpen = true;
    this.selectedProductId = productId;
    this._ProductoService.getProducto(productId).subscribe({
      next: (response: any) => {
        console.log(response);
        this.producto = response.data;
        if (this.producto && this.producto.imgen) {
          this.getProductImage(this.producto.imgen);
        }
      },
      error: (error: any) => {
        console.error("Error al obtener el producto", error);
      }
    })
  }

  getProductImage(filename: string) {
    this._ProductoService.getImage(filename).subscribe({
      next: (imageBlob: Blob) => {
        // Crear una URL local para la imagen Blob y devolverla
        const imageUrl = URL.createObjectURL(imageBlob);
        this.previsualizacion = imageUrl;
      },
      error: (error: any) => {
        console.error("Error al obtener la imagen", error);
      }
    });
  }

  captureFile(event: any) {
    let file = event.target.files[0];
    this.fileSelected = file;
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e: any) => {
      this.previsualizacion = e.target.result;
    }
  }

  closeShowModal() {
    this.isShowModalOpen = false;
    this.selectedProductId = null;
  }

  isAumentarModalOpen: boolean = false;

  openAumentarModal(productId: number) {
    this.selectedProductId = productId;
    this.cantidad = 0;
    this.isAumentarModalOpen = true;
  }

  closeAumentarModal() {
    this.isAumentarModalOpen = false;
    this.selectedProductId = null;
  }


  isDisminuirModalOpen: boolean = false;

  openDisminuirModal(productId: number) {
    this.isDisminuirModalOpen = true;
    this.selectedProductId = productId;
  }

  closeDisminuirModal() {
    this.isDisminuirModalOpen = false;
    this.selectedProductId = null;
  }

  aumentarInventario(form: any) {
    const cantidades = form.value.cantidad; // Obtener la cantidad del formulario

    if (this.selectedProductId && cantidades > 0) {
      this._InventarioService.aumentarInventario(this.selectedProductId, cantidades).subscribe({
        next: () => {
          Swal.fire('Éxito', 'Inventario aumentado correctamente', 'success');
          this.closeAumentarModal(); // Cerrar el modal después de actualizar
        },
        error: (error: any) => {
          Swal.fire('Error', 'No se pudo aumentar el inventario', 'error');
          console.error(error);
        },
      });
    } else {
      Swal.fire('Advertencia', 'Por favor ingrese una cantidad válida', 'warning');
    }
  }


  disminuirInventario(form: any) {
    const cantidades = form.value.cantidad; // Obtenemos la cantidad del formulario
    if (this.selectedProductId && cantidades > 0) {
      this._InventarioService.disminuirInventario(this.selectedProductId, cantidades).subscribe({
        next: () => {
          Swal.fire('Éxito', 'Inventario disminuido correctamente', 'success');
          this.getInventarios(); // Refresh inventory list
          this.closeDisminuirModal(); // Close modal
        },
        error: (error: any) => {
          Swal.fire('Error', 'No se pudo disminuir el inventario', 'error');
          console.error(error);
        }
      });
    } else {
      Swal.fire('Advertencia', 'Por favor ingrese una cantidad válida', 'warning');
    }
  }






}
