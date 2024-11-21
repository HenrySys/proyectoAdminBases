import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { OnInit } from '@angular/core';
import { Producto } from '../../../../models/producto';
import { pedidoProducto } from '../../../../models/pedidoProducto';
import { ProductoService} from '../../../../services/producto.service';
import { CategoriaService } from '../../../../services/categoria.service';
import { PedidoProductoService } from '../../../../services/pedidoProducto.service';
import { PedidoService } from '../../../../services/pedido.service';
import { Router } from '@angular/router';
import { Pedido } from '../../../../models/pedido';
import { initFlowbite } from 'flowbite';

import { th } from 'date-fns/locale';

@Component({
  selector: 'app-orders',
  standalone: true,
  imports: [FormsModule, CommonModule, RouterLink],
  templateUrl: './orders.component.html',
  styleUrl: './orders.component.css'
})
export class OrdersComponent implements OnInit {

  pedidos: Array<Pedido> = [];
  pedido: Pedido | undefined;
  pedidoProductosLists: pedidoProducto[] = [];
  productos: Producto[] = [];


  constructor(
    private ProductoService: ProductoService,
    private CategoriaService: CategoriaService,
    private PedidoProductoService: PedidoProductoService,
    private PedidoService: PedidoService,
    private router: Router
  ) {
    this.pedidos = [];
    this.pedido = new Pedido(1, 1, 1, 1, 1, new Date(),"","");


  }

  ngOnInit(): void {
    this.getOrders();
    this.getPedidoProductos();
    initFlowbite();


  }


  // getOrders() {
  //   this.PedidoService.getPedidos().subscribe({
  //     next: (response: any) => {
  //       console.log("hola", response);
  //       this.pedidos = response["data"];
  //     },
  //     error: (error: any) => {
  //       console.log(error);
  //     }
  //   });
  // }

  getOrders(){
    this.PedidoService.getPedidos().subscribe({
      next: (response: any) => {
        console.log("hola", response);
        this.pedidos = response["data"];
      },
      error: (error: any) => {
        console.log(error);
      }
    });

  }

  getPedidoProductos() {
    this.PedidoProductoService.getPedidoServicios().subscribe({
      next: (response: any) => {
        console.log("Prodcutos pedidos", response);
        this.pedidoProductosLists = response["data"];
      },
      error: (error: any) => {
        console.log(error);
      }
    });
  }

  getPedidoById(id: number): Pedido | undefined{
    this.pedido = this.pedidos.find((pedido) => pedido.id == id);
    return this.pedido;
  }


  getPPOfPedido(id: number): void {
    let pedidoProductos = this.pedidoProductosLists.filter((pedidoProducto) => pedidoProducto.idPedido == id);

    if (pedidoProductos.length > 0) {
      console.log("pedidoProductos", pedidoProductos);

      let productos = [];
      let pedidosEncontrados = [];
      let pedidosGuardados = false;

      pedidoProductos.forEach(pedidoProducto => {
        this.ProductoService.getProducto(pedidoProducto.idProducto).subscribe({
          next: (response: any) => {
            productos.push(response["data"]);
            console.log("Producto de pedido:", pedidoProducto.idPedido, response["data"]);

            this.pedido = this.getPedidoById(pedidoProducto.idPedido);
            if (this.pedido && !pedidosGuardados) {
              pedidosEncontrados.push(this.pedido);
            } else {
              console.log("No se encontró el pedido para idPedido:", pedidoProducto.idPedido);
            }

            if (productos.length === pedidoProductos.length) {
              localStorage.setItem('orders', JSON.stringify(productos));
              if (!pedidosGuardados) {
                localStorage.setItem('pedido', JSON.stringify(pedidosEncontrados[0])); // Almacena solo un objeto
                pedidosGuardados = true;
              }
              this.router.navigate(['/dashboard/tables/pay-order']);
            }
          },
          error: (error: any) => {
            console.log("Error al obtener el producto para idProducto:", pedidoProducto.idProducto, error);
          }
        });
      });
    } else {
      console.log("No hay pedidoProducto con idPedido:", id);
    }
  }
}




