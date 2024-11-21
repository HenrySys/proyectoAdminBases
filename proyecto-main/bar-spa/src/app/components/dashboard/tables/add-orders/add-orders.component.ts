import { Component } from '@angular/core';
import { ProductoService } from '../../../../services/producto.service';
import { Producto } from '../../../../models/producto';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { CategoriaService } from '../../../../services/categoria.service';
import { categoria } from '../../../../models/categoria';
import { timer } from 'rxjs';
import Swal from 'sweetalert2';
import { server } from '../../../../services/global';
import { OnInit } from '@angular/core';
import { initFlowbite } from 'flowbite';
import { pedidoProducto } from '../../../../models/pedidoProducto';
import { PedidoProductoService } from '../../../../services/pedidoProducto.service';
import { PedidoService } from '../../../../services/pedido.service';
import { Router, RouterLink } from '@angular/router';


@Component({
  selector: 'app-add-orders',
  standalone: true,
  imports: [FormsModule, CommonModule, RouterLink],
  templateUrl: './add-orders.component.html',
  styleUrl: './add-orders.component.css'
})
export class AddOrdersComponent implements OnInit {

  public producto: Producto;
  public productoLists: Producto[];

  public categoria: categoria;
  public categoriaLists: categoria[] = [];

  private status: number;
  public pedidosConProductos: Producto[] = [];
  public fileSelected: any;
  public previsualizacion: string = "";
  public imageUrl: any = "";
  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";
  public pedidoProducto: pedidoProducto;


  constructor(
    private _ProductoService: ProductoService,
    private _CategoriaService: CategoriaService,
    private _PedidoProductoService: PedidoProductoService,
    private _PedidoService: PedidoService,
    private router: Router


  ) {
    this.producto = new Producto(1, 1, 1, "", "", 1, "");
    this.pedidoProducto = new pedidoProducto(1, 1, 1, "", "");
    this.categoria = new categoria(1, "");
    this.status = -1;
    this.productoLists = [];
    this.filteredProduct = this.productoLists;


  }

  ngOnInit(): void {
    initFlowbite();
    this.getProducts();
    this.visibleData();
    this.pageNumbers();
    this.getCategories();
    this.getOrdersFromStorage();
    // debugger;
  }



  isDeleteModalOpen: boolean = false;
  openDeleteModal() {
    this.isDeleteModalOpen = true;
  }
  closeDeleteModal() {
    this.isDeleteModalOpen = false;
  }


  isActionDrowdownOpen: boolean = false;
  toggleDropdowns2() {
    this.isActionDrowdownOpen = this.isActionDrowdownOpen ? false : true;
  }

  deleteOrder() {
    let pedidoStor = localStorage.getItem('pedido');
    if (pedidoStor) {
      let pedido: any = JSON.parse(pedidoStor);
      let idPedido = pedido.id;
      this._PedidoService.delete(idPedido).subscribe({
        next: (response: any) => {
          console.log(response);
          this.clearOdresFromStorage();
          this.clearOrderFromStorage();
          this.closeDeleteModal();
          Swal.fire({
            icon: 'success',
            title: 'Pedido eliminado correctamente',
            showConfirmButton: false,
            timer: 1000
          }).then(() => {
            this.router.navigate(['/dashboard/tables']);
          });
        },
        error: (error: any) => {
          console.error("Error al eliminar el pedido", error);
          Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el pedido',
            showConfirmButton: false,
            timer: 1500
          });
        }
      });
    }
  }

  filterByCategory(id: number) {
    if (id == 0) {
      this.filteredProduct = this.productoLists;
    } else {
      this.filteredProduct = this.productoLists.filter((product) => product.idCategoria == id);
    }
    this.currentPage = 1;
  }

  issDropdownOpen: boolean = false;

  toggleDropdowns() {
    this.issDropdownOpen = this.issDropdownOpen ? false : true;
  }

  getCategoryOfProduct(id: number): string {
    let categoria = this.categoriaLists.find((categoria) => categoria.id == id);
    return categoria ? categoria.nombre : "No asignada";
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

  getProducts() {
    this._ProductoService.getProductos().subscribe({
      next: (response: any) => {
        console.log(response);
        this.productoLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredProduct = this.productoLists;
        this.visibleData();
      },
      error: (error: any) => {
        console.error("Error al obtener los Productos", error);
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
  filteredProduct: Array<Producto> = [];

  visibleData() {
    let start = (this.currentPage - 1) * this.pageSize;
    let end = start + this.pageSize;
    return this.filteredProduct.slice(start, end);
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
    let totalPage = Math.ceil(this.productoLists.length / this.pageSize);
    let pageNumArray = new Array(totalPage);
    return pageNumArray;

  }
  filterData(searchTerm: string) {
    this.filteredProduct = this.productoLists.filter((item) => {
      return Object.values(item).some((value) => {
        return typeof value === 'string' && value.toLowerCase().includes(searchTerm.toLowerCase());
      });
    });

  }
  goToPage(page: number) {
    this.currentPage = page;
    this.visibleData();
  }

  // Método para obtener la imagen asociada a un usuario
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

  addOrderFromStorage(order: Producto) {
    // Obtén el pedido almacenado (si existe)
    const pedidoStor = JSON.parse(localStorage.getItem('pedido') || '{}');

    // Verifica si tanto el pedido almacenado como el nuevo pedido tienen un id
    if (pedidoStor?.id && order?.id) {
      // Crea un nuevo objeto para el pedidoProducto
      const pedidoProducto = {
        idPedido: pedidoStor.id,
        idProducto: order.id,
        estado: "En Proceso"
      };

      console.log('Objeto que se guardará en localStorage:', pedidoProducto);

      // Verifica si el pedido ya tiene productos almacenados
      let pedidoProductos = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

      // Si no tiene productos, inicializa el array de productos
      if (!Array.isArray(pedidoProductos)) {
        pedidoProductos = [];
      }

      // Agregar el nuevo producto al array de productos
      pedidoProductos.push(pedidoProducto);

      // Guarda la lista de productos en localStorage
      localStorage.setItem('pedidoProductos', JSON.stringify(pedidoProductos));

      // También puedes guardar el pedido completo (si lo necesitas)
      localStorage.setItem('pedido', JSON.stringify(pedidoStor)); // Si el pedido ya tiene productos, puedes guardarlo también
      this.getOrdersFromStorage();
    }
  }



  getOrdersFromStorage() {
    const pedidoProductos = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

    // Asegúrate de que no haya productos duplicados al cargar
    if (pedidoProductos && pedidoProductos.length > 0) {
      this.pedidosConProductos = [];  // Asegúrate de limpiar el arreglo antes de llenarlo

      pedidoProductos.forEach((order: { idProducto: number; idPedido: string; estado: string }) => {
        const productoId = order.idProducto;

        // Llama al servicio para obtener los detalles del producto
        this._ProductoService.getProducto(productoId).subscribe(response => {
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






  // Cuenta cuántos productos hay en el pedido almacenado
  NumOrders() {
    // Recupera los productos almacenados bajo la clave 'pedidoProductos'
    let orders = JSON.parse(localStorage.getItem('pedidoProductos') || '[]');

    // Retorna la cantidad de productos en el array
    return orders.length;
  }

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

  submitOrder() {
    // Obtén los productos del pedido desde el localStorage
    let pedidosProductos: pedidoProducto[] = JSON.parse(localStorage.getItem('pedidoProductos') || '[]'); // Asegúrate de obtener los productos guardados

    // Verifica si hay productos en el pedido
    if (pedidosProductos.length > 0) {
      // Recorre los productos y guárdalos uno por uno
      pedidosProductos.forEach((pp: any) => {
        // Crear un nuevo objeto pedidoProducto correctamente
        let pedidoProducto = {
          idPedido: pp.idPedido,    // idPedido
          idProducto: pp.idProducto,  // idProducto
          descripcion: '',             // descripción (puedes agregarla según corresponda)
          estado: 'En Proceso'    // estado
        };

        console.log(pedidoProducto); // Verifica si ahora es un objeto

        // Llama al servicio para guardar el pedidoProducto en la base de datos
        this._PedidoProductoService.store(this.pedidoProducto).subscribe({
          next: (response: any) => {
            console.log("Pedido guardado con éxito", response);

            // Limpiar los datos del localStorage después de guardar
            this.clearOdresFromStorage();
            this.clearOrderFromStorage();

            // Redirigir a la página de mesas después de guardar el pedido
            this.router.navigate(['/dashboard/tables']);

            // Mostrar un mensaje de éxito
            Swal.fire({
              icon: 'success',
              title: 'Pedido guardado correctamente',
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              this.router.navigate(['/dashboard/tables']);
            });
          },
          error: (error: any) => {
            console.error("Error al guardar el pedido", error);
          }
        });
      });
    } else {
      // Si no hay productos en el pedido, mostrar un mensaje de error
      Swal.fire({
        icon: 'error',
        title: 'No hay productos en el pedido',
        showConfirmButton: false,
        timer: 1500
      });
    }
  }



}
