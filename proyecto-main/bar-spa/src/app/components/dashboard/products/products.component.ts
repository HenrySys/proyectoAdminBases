import { Component, OnInit } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { Producto } from '../../../models/producto';
import { categoria } from '../../../models/categoria';
import { ProductoService } from '../../../services/producto.service';
import { CommonModule } from '@angular/common';
import { timer } from 'rxjs';
import { server } from '../../../services/global';
import Swal from 'sweetalert2';
import { CategoriaService } from '../../../services/categoria.service';
import { initFlowbite } from 'flowbite';
import { ProveedorService } from '../../../services/proveedor.service';
import { Proveedor } from '../../../models/proveedor';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';



@Component({
  selector: 'app-products',
  standalone: true,
  imports: [FormsModule, CommonModule,ReactiveFormsModule],
  templateUrl: './products.component.html',
  styleUrl: './products.component.css'
})
export class ProductsComponent implements OnInit {

  productoForm!: FormGroup;
  public producto: Producto;
  public category: categoria;
  private status: number;
  public productoLists: Producto[];
  public fileSelected: any;
  public previsualizacion: string = "";
  public imageUrl: any = "";
  public peticionDirectaImgUrl: string = server.url + "producto/getimage/";
  public categoryLists: categoria[] = [];
  public proveedor: Proveedor;
  public proveerdorLists :Proveedor[] = [];
  

  constructor(
    private ProductoService: ProductoService,
    private CategoriaService: CategoriaService,
    private ProvedorService: ProveedorService,
    private fb: FormBuilder

  ) {
    this.producto = new Producto(1, 1, 1, "", "", 1, "");
    this.category = new categoria(1, "");
    this.status = -1;
    this.productoLists = [];
    this.filteredProduct = this.productoLists;
    this.proveedor = new Proveedor(1,"","","")

  }

  ngOnInit(): void {
    this.getProducts();
    this.visibleData();
    this.pageNumbers();
    this.getCategories();
    this.getProviders();
    this.initializeForm();
    
    // debugger;
  }

  initializeForm() {
    this.productoForm = this.fb.group({
      producto: this.fb.group({
        nombre: ['', [Validators.required]],
        descripcion: ['', [Validators.required]],
        precio: ['', [Validators.required, Validators.min(0.01)]],
        idProveedor: ['', [Validators.required]],
        idCategoria: ['', [Validators.required]],
        imgen: ['', [Validators.required]],

      }),
      inventario: this.fb.group({
        cantidad: ['', [Validators.required, Validators.min(0)]],
        ubicacion: ['', [Validators.required]],

      }),
    });
  }
  onSubmit() {
    // Verificar si el formulario de producto es válido
    if (this.productoForm.valid) {
      const formData = this.productoForm.value; // Obtener los datos del formulario
      console.log('Formulario capturado:', JSON.stringify(formData, null, 2))
      console.log('Datos del formulario de producto:', formData);
  
      // Verificar si hay un archivo seleccionado para cargar
      if (this.fileSelected) {
        // Subir la imagen
        this.ProductoService.uploadImage(this.fileSelected).subscribe({
          next: (response: any) => {
            if (response.filename) {
              formData.producto.imgen = response.filename; // Asignar el nombre del archivo a la imagen
              // Ahora, enviar los datos del producto al backend
              this.ProductoService.store(formData).subscribe({
                next: (responseSub: any) => {
                  console.log('Producto registrado con éxito:', responseSub);
                  Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El producto se ha registrado correctamente.'
                  });
                  this.productoForm.reset(); // Resetear el formulario
                  this.changeStatus(0); // Cambiar el estado del formulario
                  this.getProducts(); // Actualizar la lista de productos
                },
                error: (error) => {
                  console.error('Error al registrar el producto:', error);
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un error al registrar el producto. Por favor, inténtalo de nuevo.'
                  });
                  this.changeStatus(2); // Cambiar el estado en caso de error
                }
              });
            } else {
              console.error('Falta el nombre del archivo en la respuesta');
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Falta el nombre del archivo en la respuesta del servidor.'
              });
            }
          },
          error: (error) => {
            console.error('Error al subir la imagen del producto:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al subir la imagen del producto. Por favor, inténtalo de nuevo.'
            });
          }
        });
      } else {
        // Si no hay archivo, solo enviar los datos del producto
        this.ProductoService.store(formData).subscribe({
          next: (response: any) => {
            console.log('Producto registrado con éxito:', response);
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El producto se ha registrado correctamente.'
            });
            this.productoForm.reset(); // Resetear el formulario
            this.changeStatus(0); // Cambiar el estado del formulario
            this.getProducts(); // Actualizar la lista de productos
          },
          error: (error) => {
            console.error('Error al registrar el producto:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al registrar el producto. Por favor, inténtalo de nuevo.'
            });
            this.changeStatus(2); // Cambiar el estado en caso de error
          }
        });
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Formulario inválido',
        text: 'Por favor, completa todos los campos requeridos.'
      });
    }
  }
  


  isOpenDeleteCorfirmationCategory: boolean = false;
  selectedCategoryId: number | null = null;

  openDeleteCorfirmationCategory(id: number) {
    this.isOpenDeleteCorfirmationCategory = true;
    this.selectedCategoryId = id;
  }

  closeDeleteCorfirmationCategory() {
    this.isOpenDeleteCorfirmationCategory = false;
    this.selectedCategoryId = null;
  }

  confirmDeleteCategory() {
    console.log("ConfirmDelete llamado");

    if (this.selectedCategoryId !== null) {
      console.log(`Eliminando categoria con ID: ${this.selectedCategoryId}`);

      this.deleteCategary(this.selectedCategoryId);
      this.closeDeleteCorfirmationCategory();
    } else {
      console.error("No se ha seleccionado ningúna categoria para eliminar");

      // Opcional: Manejar el caso donde no hay un producto seleccionado
      Swal.fire({
        icon: 'error',
        title: 'Ningúna categoria seleccionado',
        text: 'Por favor selecciona un categoria para eliminar.',
        showConfirmButton: true
      });
    }
  }

  deleteCategary(id: number) {
    this.CategoriaService.deleteCategoria(id).subscribe({
      next: (response: any) => {
        console.log(response);
        this.getCategories();
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'La categoría se ha eliminado correctamente.'
        });
      },
      error: (error: any) => {
        console.error("Error al eliminar la categoría", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al eliminar la categoría. Por favor, inténtalo de nuevo.'
        });
      }
    });
  }

  filterByCategory(id: number) {
    if (id == 0) {
      this.filteredProduct = this.productoLists;
    } else {
      this.filteredProduct = this.productoLists.filter((product) => product.idCategoria == id);
    }
    this.currentPage = 1;
  }

  onSubmitCategory(form: any) {

    this.CategoriaService.store(this.category).subscribe({
      next: (response: any) => {
        console.log(response);
        form.reset();
        this.getCategories();
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'La categoría se ha registrado correctamente.'
        });
      },
      error: (error: any) => {
        console.error("Error al registrar la categoría", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al registrar la categoría. Por favor, inténtalo de nuevo.'
        });
      }
    });
  }

  issDropdownOpen: boolean = false;

  toggleDropdowns() {
    this.issDropdownOpen = this.issDropdownOpen ? false : true;
  }

  getCategoryOfProduct(id: number): string {
    let category = this.categoryLists.find((category) => category.id == id);
    return category ? category.nombre : "No asignada";
  }

  getProviders() {
    this.ProvedorService.getProvedores().subscribe({
      next: (response: any) => {
        console.log(response);
        this.proveerdorLists = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener los proveedores", error);
      }
    });
  }

  getCategories() {
    this.CategoriaService.getCategorias().subscribe({
      next: (response: any) => {
        console.log(response);
        this.categoryLists = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener las categorías", error);
      }
    });
  }

  isTableOpen: boolean = true;
  isCardsOpen: boolean = false;

  toggleCards() {
    this.isTableOpen = false;
    this.isCardsOpen = true;
  }
  toggleTable() {
    this.isTableOpen = true;
    this.isCardsOpen = false;
  }

  isEditModalOpen: boolean = false;
  productToEdit: Producto | null = null;

  cancelEdit() {
    this.isEditModalOpen = false;
    this.productToEdit = null;
  }

  onEdit(form: any) {
    this.productToEdit = form;
    if (this.productToEdit) {
      this.isEditModalOpen = true;
      this.producto = this.productToEdit;
    }

  }

  onsubmitEdit() {
    if (this.productToEdit) {
      if (this.fileSelected) {
        this.ProductoService.uploadImage(this.fileSelected).subscribe({
          next: (response: any) => {
            if (response.filename) {
              this.producto.imgen = response.filename;
              this.ProductoService.update(this.producto).subscribe({
                next: (responseSub: any) => {
                  this.getProducts();
                  Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El producto se ha actualizado correctamente.'
                  });
                },
                error: (error: any) => {
                  console.error("Error al actualizar el producto", error);
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un error al actualizar el producto. Por favor, inténtalo de nuevo.'
                  });
                }
              });
            } else {
              console.error("Falta el nombre del archivo en la respuesta");
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Falta el nombre del archivo en la respuesta del servidor.'
              });
            }
          }, error: (error: any) => {
            console.error("Error al subir la imagen", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al subir la imagen del producto. Por favor, inténtalo de nuevo.'
            });
          }
        });
      } else {
        this.ProductoService.update(this.producto).subscribe({
          next: (response: any) => {
            this.getProducts();
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'El producto se ha actualizado correctamente.'
            });
          },
          error: (error: any) => {
            console.error("Error al actualizar el producto", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un error al actualizar el producto. Por favor, inténtalo de nuevo.'
            });
          }
        });
      }
    } else {
      console.error("No se ha seleccionado ningún producto para actualizar");
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se ha seleccionado ningún producto para actualizar. Por favor, selecciona un producto.'
      });
    }
  }

  isDeleteModalOpen: boolean = false;
  selectedProductId: number | null = null;

  openDeleteConfirmationAndCloseShowModal(productId: number) {
    this.selectedProductId = productId;
    // Abre el modal de confirmación de eliminación
    this.openDeleteConfirmation(productId);
    // Cierra el modal de vista de detalles
    this.closeShowModal();
  }


  openDeleteConfirmation(productId: number) {
    this.selectedProductId = productId;
    this.isDeleteModalOpen = true;
  }

  closeDeleteModal() {
    this.isDeleteModalOpen = false;
    this.selectedProductId = null;
  }

  confirmDelete() {
    console.log("ConfirmDelete llamado");

    if (this.selectedProductId !== null) {
      console.log(`Eliminando producto con ID: ${this.selectedProductId}`);

      this.ProductoService.delete(this.selectedProductId).subscribe({
        next: (response: any) => {
          console.log("Producto eliminado con éxito:", response);
          this.getProducts();
          this.closeDeleteModal();

          // Mostrar el SweetAlert después de que la eliminación sea exitosa
          Swal.fire({
            icon: 'success',
            title: 'Producto eliminado',
            showConfirmButton: false,
            timer: 1500
          });
        },
        error: (error: any) => {
          console.error("Error al eliminar el producto", error);
          // Opcional: Mostrar una alerta en caso de error
          Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el producto',
            text: 'No se pudo eliminar el producto. Inténtalo de nuevo.',
            showConfirmButton: true
          });
        }
      });
    } else {
      console.error("No se ha seleccionado ningún producto para eliminar");

      // Opcional: Manejar el caso donde no hay un producto seleccionado
      Swal.fire({
        icon: 'error',
        title: 'Ningún producto seleccionado',
        text: 'Por favor selecciona un producto para eliminar.',
        showConfirmButton: true
      });
    }
  }


  isShowModalOpen: boolean = false;

  openShowModal(productId: number) {
    this.isShowModalOpen = true;
    this.selectedProductId = productId;
    this.ProductoService.getProducto(productId).subscribe({
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

  closeShowModal() {
    this.isShowModalOpen = false;
    this.selectedProductId = null;
  }

 

  getProducts() {
    this.ProductoService.getProductos().subscribe({
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

  captureFile(event: any) {
    let file = event.target.files[0];
    this.fileSelected = file;
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e: any) => {
      this.previsualizacion = e.target.result;
    }
  }

  subirArchivo(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.ProductoService.uploadImage(file).subscribe({
        next: (response: any) => {
          console.log(response);
          if (response.filename) {
            this.producto.imgen = response.filename;
            this.ProductoService.store(this.producto).subscribe({
              next: (responseSub: any) => {
                console.log(responseSub);
                this.getProducts();
              },
              error: (error: any) => {
                console.error("Error al registrar el producto", error);
              }
            });
          } else {
            console.error("Falta el nombre del archivo en la respuesta");
          }
        },
        error: (error: any) => {
          console.error("Error al subir la imagen", error);
        }
      });
    } else {
      console.error("No se ha seleccionado ningún archivo");
    }
  }
  

}
