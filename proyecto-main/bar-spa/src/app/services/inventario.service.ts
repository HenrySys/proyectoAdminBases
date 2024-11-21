import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { server } from "./global";
import { Inventario } from "../models/inventario";
import { Observable } from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class InventarioService {
    public url:string;
    constructor(private _http:HttpClient){
        this.url = server.url;
    }


    update(inventario:Inventario):Observable<any>{
        let userJson = JSON.stringify(inventario);
        let params = 'data='+userJson;
        let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};
        return this._http.post(this.url+'inventario/actualizar/'+inventario.id,params,options);
    }



    getInventarios():Observable<any>{
      let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};
      return this._http.get(this.url+'inventario/index',options);
    }

    getInventario(id:number):Observable<any>{
      let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};

      return this._http.get(this.url+'inventario/'+id,options);
    }

    aumentarInventario(idProducto: number, cantidad: number): Observable<any> {
        let data = { idProducto, cantidad };
        let params = 'data=' + JSON.stringify(data);
        let headers;
        let bearerToken = sessionStorage.getItem('token');
        if (bearerToken) {
            headers = new HttpHeaders()
                .set('Content-Type', 'application/x-www-form-urlencoded')
                .set('bearertoken', bearerToken);
        } else {
            headers = new HttpHeaders()
                .set('Content-Type', 'application/x-www-form-urlencoded');
        }
        let options = { headers };
        return this._http.post(this.url + 'inventario/aumentar', params, options);
    }

    disminuirInventario(idProducto: number, cantidad: number): Observable<any> {
        let data = { idProducto, cantidad };
        let params = 'data=' + JSON.stringify(data);
        let headers;
        let bearerToken = sessionStorage.getItem('token');
        if (bearerToken) {
            headers = new HttpHeaders()
                .set('Content-Type', 'application/x-www-form-urlencoded')
                .set('bearertoken', bearerToken);
        } else {
            headers = new HttpHeaders()
                .set('Content-Type', 'application/x-www-form-urlencoded');
        }
        let options = { headers };
        return this._http.post(this.url + 'inventario/disminuir', params, options);
    }



}
