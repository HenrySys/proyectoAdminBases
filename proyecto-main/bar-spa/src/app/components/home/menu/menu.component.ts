import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Producto } from '../../../models/producto';
import { ProductoService } from '../../../services/producto.service';
import { server } from '../../../services/global';
import { categoria } from '../../../models/categoria';
import { CategoriaService } from '../../../services/categoria.service';

@Component({
  selector: 'app-menu',
  standalone: true,
  imports: [FormsModule, CommonModule],
  templateUrl: './menu.component.html',
  styleUrls: ['./menu.component.css']
})
export class MenuComponent implements OnInit {


  public fileSelected: any;
  public productosLists: Producto[];
  public producto: Producto;
  public imageUrl: any = "";
  public previsualizacion: string = "";
  public categoria: categoria;
  public categoriaLists: categoria[];
  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";


  constructor(private ProductoService: ProductoService) {

    this.producto = new Producto(0, 0, 0, "","",0,"" );
    this.productosLists = [];
    this.filteredProduct = this.productosLists;
    this.categoria = new categoria(0, '');
    this.categoriaLists = [];
  }

  ngOnInit(): void {
    this.getProducts();

  }
  currentPage: number = 1;
  pageSize: number = 10;
  filteredProduct: Array<Producto> = [];


  getProducts() {
    this.ProductoService.getProductos().subscribe({
      next: (response: any) => {
        console.log(response);
        this.productosLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta


      },
      error: (error: any) => {
        console.error("Error al obtener los Productos", error);
      }
    });
  };

  getProductImage(filename: string) {
    this.ProductoService.getImage(filename).subscribe({
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

  getCategoryOfService(id: number): string {
    let categoria = this.categoriaLists.find((categoria) => categoria.id == id);
    return categoria ? categoria.nombre : "No asignada";
  }



}

