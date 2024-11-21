import { Component } from '@angular/core';
import { RouterOutlet, RouterLink, NavigationEnd } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { server } from '../../../../services/global';
import { OnInit } from '@angular/core';
import { format } from 'date-fns';
import { Cliente } from '../../../../models/cliente';
import { ClienteService } from '../../../../services/cliente.service';
import { Pedido } from '../../../../models/pedido';
import { PedidoService } from '../../../../services/pedido.service';
import { PedidoProductoService } from '../../../../services/pedidoProducto.service';
import { pedidoProducto } from '../../../../models/pedidoProducto';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { Producto } from '../../../../models/producto';
import { Factura } from '../../../../models/factura';
import { DetalleFactura } from '../../../../models/detalleFactura';
import { FacturaService } from '../../../../services/factura.service';
import { detalleFacturaService } from '../../../../services/detalleFactura.service';
import { catchError, forkJoin, map, Observable, of, Subscription, switchMap } from 'rxjs';
import { th } from 'date-fns/locale';
import { categoria } from '../../../../models/categoria';


@Component({
  selector: 'app-pay-order',
  standalone: true,
  imports: [RouterOutlet, RouterLink, CommonModule, FormsModule],
  templateUrl: './pay-order.component.html',
  styleUrl: './pay-order.component.css'
})

export class PayOrderComponent implements OnInit {


  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";
  fechaHoraActual: string;
  private intervalId: any;
  public cliente: Cliente;
  public clientes: Cliente[];
  public clientesCargados: boolean = false;
  public factura: Factura;
  public detalleFactura: DetalleFactura;
  public pedidoProducto: pedidoProducto;
  public producto: Producto;
  public pedidos: Pedido[];
  public pedido: Pedido;
  public pedidoProductos: any[] = [];
  public categoria: categoria;
  public categoriaLists: categoria[] = [];
  public productoLists: Producto[];
  public pedidosConProductos: Producto[] = [];



  private navigationSubscription: Subscription;
  filteredCliente: Cliente[];
  private _ProductoService: any;



  constructor(
    private _PedidoService: PedidoService,
    private _PedidoProductoService: PedidoProductoService,
    private _ClienteService: ClienteService,
    private _FacturaService: FacturaService,
    private _DetalleFacturaService: detalleFacturaService,
    private router: Router


  ) {
    
    this.fechaHoraActual = this.getFormattedDate(new Date());
    this.cliente = new Cliente(1,1,"","","","","");
    this.clientes = [];
    this.clienteExistente = this.clientes;
    this.factura = new Factura(1, "", "", 1, 1, 1);
    this.detalleFactura = new DetalleFactura(1, 1, 1, 1);
    this.pedidoProducto = new pedidoProducto(1, 1, 1, "","");
    this.producto = new Producto(1,1,1,"","",1,"");
    this.pedidos = [];
    this.pedido = new Pedido(1,1,1,1,1, new Date(), "","")
    this.filteredCliente = this.clientes;
    this.categoria = new categoria(1, "");
    this.productoLists = [];
    this.filteredProduct = this.productoLists;

    this.navigationSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        if (this.router.url.includes('/dashboard/tables/pay-order')) {
          location.reload();
        }
      }
    });


  }
  ngOnInit(): void {
    this.getOrdersFromStorage();
    this.getClientes();
    this.NumOrders();
    //this.getPedidosServicios();


    this.intervalId = setInterval(() => {
     this.fechaHoraActual = this.getFormattedDate(new Date());
    }, 1000);
  }

  
  filteredProduct: Array<Producto> = [];


  clienteExistente: Array<Cliente> = [];



  filterData(searchTerm: string) {
    this.filteredCliente = this.clientes.filter((item) => {
      return Object.values(item).some((value) => {
        return typeof value === 'string' && value.toLowerCase().includes(searchTerm.toLowerCase());
      });
    });
  }


  filterByCeldula(id: number) {
    if (id == 0) {
      this.filteredCliente = this.clientes;
    } else {
      this.filteredCliente = this.clientes.filter((cliente) => cliente.id == id);
      return ;
    }
  }



  isDeleteModalOpen: boolean = false;
  openDeleteModal() {
    this.isDeleteModalOpen = true;
  }
  closeDeleteModal() {
    this.isDeleteModalOpen = false;
  }


  



 

  getCategoryOfProduct(id: number): string {
    let categoria = this.categoriaLists.find((categoria) => categoria.id == id);
    return categoria ? categoria.nombre : "No asignada";
  }

  getProducts() {
    this._ProductoService.getProductos().subscribe({
      next: (response: any) => {
        console.log(response);
        this.productoLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredProduct = this.productoLists;
      },
      error: (error: any) => {
        console.error("Error al obtener los Productos", error);
      }
    });
  };


  deleteOrderFromStorageById(id: number) {
    console.log('Eliminando pedido con ID:', id);

    // Obtén los datos actuales del localStorage
    const orders = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

    // Depuración: muestra los datos de 'orders' y el ID que estamos buscando
    console.log('Pedidos en storage:', orders);
    console.log('ID a eliminar:', id);

    // Encuentra el índice del pedido con el ID proporcionado
    const index = orders.findIndex((order: pedidoProducto) => {
      console.log('Comparando', order.idPedido, 'con', id);
      return +order.idPedido === id; // Cambié de order.id a order.idPedido
    });

    // Verifica si el índice es válido y elimina el elemento
    if (index !== -1) {
      orders.splice(index, 1); // Elimina el elemento correctamente
      localStorage.setItem('pedidoProductos', JSON.stringify(orders)); // Actualiza el localStorage
      this.getOrdersFromStorage(); // Recarga los pedidos visibles
      console.log(`Pedido con ID: ${id} eliminado correctamente.`);
    } else {
      console.error(`No se encontró un pedido con ID: ${id}`);
    }
  }


  ngOnDestroy(): void {
    if (this.intervalId) {
      clearInterval(this.intervalId); // Limpia el intervalo cuando el componente se destruya
    }
  }

  private getFormattedDate(date: Date): string {
    return format(date, 'PPpp'); // Formato de fecha y hora legible
  }

  getOrdersFromStorage() {
    const pedidoProductos = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

    // Asegúrate de que no haya productos duplicados al cargar
    if (pedidoProductos && pedidoProductos.length > 0) {
      this.pedidosConProductos = [];  // Asegúrate de limpiar el arreglo antes de llenarlo

      pedidoProductos.forEach((order: { idProducto: number; idPedido: string; estado: string }) => {
        const productoId = order.idProducto;

        // Llama al servicio para obtener los detalles del producto
        this._ProductoService.getProducto(productoId).subscribe((response: { data: Producto }) => {
          if (response && response.data) {
            // Combina los datos del pedido con los detalles del producto
            this.pedidosConProductos.push({
              ...response.data, // Solo la propiedad `data` del producto
              ...order, // Si deseas incluir los datos adicionales del pedido
            });
            console.log('Producto cargado:', response.data); // Verifica los detalles del producto
          } else {
            console.error(`No se encontró el producto con ID ${productoId}`);
          }
        });
      });
    }
  }

  getClientes() {
    this._ClienteService.getClientes().subscribe({
      next: (response: any) => {
        console.log(response);
        this.clientes = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredCliente = this.clientes;
        this.clientesCargados = true;
      },
      error: (error: any) => {
        console.error("Error al obtener los clientes", error);
      }
    });
  };  

  // Cuenta cuántos productos hay en el pedido almacenado
  NumOrders() {
    // Recupera los productos almacenados bajo la clave 'pedidoProductos'
    let orders = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

    // Retorna la cantidad de productos en el array
    return orders.length;
  }

  

  clearOdresFromStorage() {
    localStorage.removeItem('pedidoProductos');
    // Limpia la lista de productos mostrados
    this.pedidosConProductos = [];

    // Recarga los datos desde el almacenamiento (ahora vacío)
    this.getOrdersFromStorage();
  }


  clearOrderFromStorage() {
    localStorage.removeItem('pedido');
  }

 
 


}








