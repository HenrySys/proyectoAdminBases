import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { server } from "./global";
import { pedidoProducto } from "../models/pedidoProducto";
import { Observable } from "rxjs";
import {  Pedido } from "../models/pedido";

@Injectable({
  providedIn: 'root'
})
export class PedidoProductoService {
  public url: string;

  constructor(private _http: HttpClient) {
    this.url = server.url;
  }

  store(pedidoProducto: pedidoProducto): Observable<any> {
    let userJson = JSON.stringify(pedidoProducto);
    let params = 'data=' + userJson;
    let headers;
    let bearerToken = sessionStorage.getItem('token');
    if (bearerToken) {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('bearertoken', bearerToken);
    } else {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');
    }
    let options = { headers };
    return this._http.post(this.url + 'pedidoProducto/store', params, options);
  }

  getPedidoServicios(): Observable<any> {
    let headers;
    let bearerToken = sessionStorage.getItem('token');
    if (bearerToken) {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('bearertoken', bearerToken);
    } else {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');
    }
    let options = { headers };
    return this._http.get(this.url + 'pedidoProducto/' , options);
  }

  delete(id: number): Observable<any> {
    let headers;
    let bearerToken = sessionStorage.getItem('token');
    if (bearerToken) {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('bearertoken', bearerToken);
    } else {
      headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');
    }
    let options = { headers };
    return this._http.delete(this.url + 'pedidoProducto/' + id, options);
  }


}
