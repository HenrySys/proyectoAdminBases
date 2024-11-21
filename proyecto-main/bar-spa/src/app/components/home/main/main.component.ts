import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Reserva } from '../../../models/reserva';
import { ReservaService } from '../../../services/reserva.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Producto } from '../../../models/producto';
import { ProductoService } from '../../../services/producto.service';
import { categoria } from '../../../models/categoria';
import { CategoriaService } from '../../../services/categoria.service';
import { Mesa } from '../../../models/mesa';
import { MesaService } from '../../../services/mesa.service';
import { server } from '../../../services/global';


@Component({
  selector: 'app-main',
  standalone: true,
  imports: [RouterLink, FormsModule, CommonModule],
  templateUrl: './main.component.html',
  styleUrl: './main.component.css'
})

export class MainComponent implements OnInit {
  public reserva: Reserva;
  public reservaLists: Reserva[];

  public producto: Producto;
  public productoLists: Producto[];

  public categoria: categoria;
  public categoriaLists: categoria[];

  public mesa: Mesa;
  public MesaLists: Mesa[];

  public previsualizacion: string = "";
  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";

  constructor(
    private _ReservaService: ReservaService,
    private _ProductoService: ProductoService,
    private _CategoriaService: CategoriaService,
    private _MesaService: MesaService

  ) {

    this.reserva = new Reserva(0, 0, 0, 0, new Date(), "", "");
    this.reservaLists = [];
    this.producto = new Producto(0, 0, 0, "", "", 0, "");
    this.productoLists = [];
    this.categoria = new categoria(1, "");
    this.categoriaLists = [];
    this.mesa = new Mesa(1, 1, 1, "");
    this.MesaLists= [];
  }

  ngOnInit(): void {
    //this.getReservas();
    //this.getServices();
    //this.getMesas();
    //this.getCategories();

  }


  store() {
    console.log("entre");
    this.reserva.fecha = new Date();
    this.reserva.hora = new Date().toTimeString().split(' ')[0];
    //this.reserva.estado = 'pendiente';
    let user = JSON.parse(sessionStorage.getItem('identity')??'{}');
    console.log(user);
    this.reserva.idUser = user.iss;
    let MesaDisponible=this.getMesasDisponible()?? new Mesa(1,1,1,"Disponible");
    this.reserva.idMesa = MesaDisponible.id;
    this._ReservaService.store(this.reserva).subscribe({
      next: (res) => {
        console.log(res);
        MesaDisponible.estado="Ocupado"
        this._MesaService.update(MesaDisponible).subscribe({
          next: (response: any) => {
            console.log(response);
            this.getMesas();
          },
          error: (error: any) => {
            console.error("Error al actualizar la mesa", error);
          }

        });
      },
      error: (err) => {
        console.error('Error al guardar la reserva', err);
      }
    });
  }

  getReservas() {
    this._ReservaService.getReservas().subscribe({
      next: (res) => {
        console.log(res);
        this.reservaLists = res;
      },
      error: (err) => {
        console.error('Error al obtener las reservas', err);
      }
    });
  }

  getServices() {
    this._ProductoService.getProductos().subscribe({
      next: (response: any) => {
        console.log(response);
        this.productoLists = response['data'];
      },
      error: (error: any) => {
        console.error("Error al obtener los productos", error);
      }
    });
  }

  getCategoryOfService(id: number): string {
    let categoria = this.categoriaLists.find((categoria) => categoria.id == id);
    return categoria ? categoria.nombre : "No asignada";
  }

  getMesas(){
    this._MesaService.getMesas().subscribe({
      next: (res:any) => {
        this.MesaLists = res['data'];

      },
      error: (error:any) => {
        console.error("Error al obtener los productos", error);
      }
    });
  }

  getMesasDisponible(){
    let mesa = this.MesaLists.find((mesa) => mesa.estado === "Disponible");
      return mesa;
  }
  getCategories() {
    this._CategoriaService.getCategorias().subscribe({
      next: (response: any) => {
        console.log(response);
        this.categoriaLists = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener las categorías", error);
      }
    });
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




}
