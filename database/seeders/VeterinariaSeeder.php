<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class VeterinariaSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalogos();
        $this->seedGestionPersonas();
        $this->seedPacientesAgenda();
        $this->seedCitas();
        $this->seedAtenciones();
        $this->seedHistorialClinico();
        $this->seedMedicamentos();
        $this->seedCompras();
        $this->seedFacturacion();
        $this->seedVentasInventario();
        $this->seedPaquetes();
        $this->seedRegistroAccesos();
        $this->resetSequence();
    }

    private function seedCatalogos(): void
    {
        DB::table('personas')->insert([
            ['id_persona' => 1, 'documento' => '12345678', 'nombre' => 'Juan', 'apellido1' => 'Perez', 'apellido2' => 'Gomez', 'telefono' => '71234567', 'email' => 'juan@email.com', 'direccion' => 'Av. Libertad N° 123'],
            ['id_persona' => 2, 'documento' => '87654321', 'nombre' => 'Maria', 'apellido1' => 'Lopez', 'apellido2' => 'Flores', 'telefono' => '71234568', 'email' => 'maria@email.com', 'direccion' => 'Calle Sucre N° 456'],
            ['id_persona' => 3, 'documento' => '11223344', 'nombre' => 'Carlos', 'apellido1' => 'Mendoza', 'apellido2' => 'Rios', 'telefono' => '71234569', 'email' => 'carlos@email.com', 'direccion' => 'Av. America N° 789'],
            ['id_persona' => 4, 'documento' => '99887766', 'nombre' => 'Ana', 'apellido1' => 'Torres', 'apellido2' => 'Vargas', 'telefono' => '71234570', 'email' => 'ana@email.com', 'direccion' => 'Calle Bolivar N° 321'],
            ['id_persona' => 5, 'documento' => '55443322', 'nombre' => 'Luis', 'apellido1' => 'Rojas', 'apellido2' => 'Cruz', 'telefono' => '71234571', 'email' => 'luis@email.com', 'direccion' => 'Av. San Martin N° 654'],
            ['id_persona' => 6, 'documento' => '66778899', 'nombre' => 'Sofia', 'apellido1' => 'Garcia', 'apellido2' => 'Mamani', 'telefono' => '71234572', 'email' => 'sofia@email.com', 'direccion' => 'Calle 6 de Agosto N° 987'],
            ['id_persona' => 7, 'documento' => '44332211', 'nombre' => 'Pedro', 'apellido1' => 'Fernandez', 'apellido2' => 'Quispe', 'telefono' => '71234573', 'email' => 'pedro@email.com', 'direccion' => 'Av. Heroinas N° 159'],
            ['id_persona' => 8, 'documento' => '77665544', 'nombre' => 'Laura', 'apellido1' => 'Gonzales', 'apellido2' => 'Paredes', 'telefono' => '71234574', 'email' => 'laura@email.com', 'direccion' => 'Calle Potosi N° 753'],
            ['id_persona' => 9, 'documento' => '88990011', 'nombre' => 'Diego', 'apellido1' => 'Rodriguez', 'apellido2' => 'Mercado', 'telefono' => '71234575', 'email' => 'diego@email.com', 'direccion' => 'Av. Busch N° 852'],
            ['id_persona' => 10, 'documento' => '33445566', 'nombre' => 'Elena', 'apellido1' => 'Martinez', 'apellido2' => 'Ortiz', 'telefono' => '71234576', 'email' => 'elena@email.com', 'direccion' => 'Calle Colon N° 456'],
            ['id_persona' => 11, 'documento' => '22334455', 'nombre' => 'Roberto', 'apellido1' => 'Vargas', 'apellido2' => 'Chavez', 'telefono' => '71234581', 'email' => 'transporte@email.com', 'direccion' => 'Av. Circunvalacion N° 555'],
            ['id_persona' => 12, 'documento' => '99001122', 'nombre' => 'Marcos', 'apellido1' => 'Inactivo', 'apellido2' => 'Siles', 'telefono' => '71234582', 'email' => 'inactivo@email.com', 'direccion' => 'Calle Falsa N° 123'],
        ]);

        DB::table('roles')->insert([
            ['id_rol' => 1, 'nombre' => 'ADMIN', 'descripcion' => 'Administrador del sistema'],
            ['id_rol' => 2, 'nombre' => 'VETERINARIO', 'descripcion' => 'Medico veterinario'],
            ['id_rol' => 3, 'nombre' => 'ESTETICO', 'descripcion' => 'Especialista en estetica canina'],
            ['id_rol' => 4, 'nombre' => 'RECEPCION', 'descripcion' => 'Recepcionista'],
            ['id_rol' => 5, 'nombre' => 'SUPER_ADMIN', 'descripcion' => 'Super Administrador con acceso total'],
            ['id_rol' => 6, 'nombre' => 'TRANSPORTE', 'descripcion' => 'Conductor de servicio a domicilio'],
        ]);

        DB::table('permisos')->insert([
            ['id_permiso' => 1, 'nombre' => 'CREAR_CITA', 'descripcion' => 'Permite crear citas'],
            ['id_permiso' => 2, 'nombre' => 'EDITAR_CITA', 'descripcion' => 'Permite editar citas'],
            ['id_permiso' => 3, 'nombre' => 'ELIMINAR_CITA', 'descripcion' => 'Permite eliminar citas'],
            ['id_permiso' => 4, 'nombre' => 'VER_HISTORIAL', 'descripcion' => 'Permite ver historial clinico'],
            ['id_permiso' => 5, 'nombre' => 'CREAR_USUARIO', 'descripcion' => 'Permite crear usuarios'],
            ['id_permiso' => 6, 'nombre' => 'GESTIONAR_INVENTARIO', 'descripcion' => 'Permite gestionar inventario'],
        ]);

        DB::table('especies')->insert([
            ['id_especie' => 1, 'nombre' => 'Perro'],
            ['id_especie' => 2, 'nombre' => 'Gato'],
            ['id_especie' => 3, 'nombre' => 'Conejo'],
            ['id_especie' => 4, 'nombre' => 'Ave'],
        ]);

        DB::table('servicios')->insert([
            ['id_servicio' => 1, 'nombre' => 'Baño', 'categoria' => 'ESTETICA', 'descripcion' => 'Baño completo con secado', 'observaciones' => null, 'precio_base' => 50.00, 'disponible_domicilio' => true, 'estado' => 'ACTIVO'],
            ['id_servicio' => 2, 'nombre' => 'Corte', 'categoria' => 'ESTETICA', 'descripcion' => 'Corte de pelo segun raza', 'observaciones' => null, 'precio_base' => 80.00, 'disponible_domicilio' => true, 'estado' => 'ACTIVO'],
            ['id_servicio' => 3, 'nombre' => 'Corte de uñas', 'categoria' => 'ESTETICA', 'descripcion' => 'Corte y limado de uñas', 'observaciones' => null, 'precio_base' => 30.00, 'disponible_domicilio' => true, 'estado' => 'ACTIVO'],
            ['id_servicio' => 4, 'nombre' => 'Consulta general', 'categoria' => 'VETERINARIA', 'descripcion' => 'Consulta veterinaria general', 'observaciones' => null, 'precio_base' => 100.00, 'disponible_domicilio' => false, 'estado' => 'ACTIVO'],
            ['id_servicio' => 5, 'nombre' => 'Vacunacion', 'categoria' => 'VETERINARIA', 'descripcion' => 'Aplicacion de vacunas', 'observaciones' => null, 'precio_base' => 150.00, 'disponible_domicilio' => false, 'estado' => 'ACTIVO'],
            ['id_servicio' => 6, 'nombre' => 'Desparasitacion', 'categoria' => 'VETERINARIA', 'descripcion' => 'Desparasitacion interna y externa', 'observaciones' => null, 'precio_base' => 120.00, 'disponible_domicilio' => false, 'estado' => 'ACTIVO'],
        ]);

        DB::table('productos')->insert([
            ['id_producto' => 1, 'nombre' => 'Shampoo Antipulgas', 'tipo' => 'INSUMO', 'categoria' => 'ESTETICA', 'marca' => 'PetCare', 'unidad_medida' => 'Litro', 'descripcion' => 'Shampoo para pulgas y garrapatas', 'precio_venta' => 45.00, 'stock_minimo' => 10.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 2, 'nombre' => 'Acondicionador', 'tipo' => 'INSUMO', 'categoria' => 'ESTETICA', 'marca' => 'PetCare', 'unidad_medida' => 'Litro', 'descripcion' => 'Acondicionador para pelo suave', 'precio_venta' => 35.00, 'stock_minimo' => 10.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 3, 'nombre' => 'Corta uñas', 'tipo' => 'HERRAMIENTA', 'categoria' => 'ESTETICA', 'marca' => 'ProPet', 'unidad_medida' => 'Unidad', 'descripcion' => 'Corta uñas profesional', 'precio_venta' => 25.00, 'stock_minimo' => 5.00, 'control_lotes' => false, 'estado' => 'ACTIVO'],
            ['id_producto' => 4, 'nombre' => 'Vacuna Rabia', 'tipo' => 'MEDICAMENTO', 'categoria' => 'VETERINARIA', 'marca' => 'VetLab', 'unidad_medida' => 'Dosis', 'descripcion' => 'Vacuna antirrabica', 'precio_venta' => 120.00, 'stock_minimo' => 20.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 5, 'nombre' => 'Vacuna Triple', 'tipo' => 'MEDICAMENTO', 'categoria' => 'VETERINARIA', 'marca' => 'VetLab', 'unidad_medida' => 'Dosis', 'descripcion' => 'Vacuna triple felina', 'precio_venta' => 150.00, 'stock_minimo' => 20.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 6, 'nombre' => 'Desparasitante Interno', 'tipo' => 'MEDICAMENTO', 'categoria' => 'VETERINARIA', 'marca' => 'VetLab', 'unidad_medida' => 'Tableta', 'descripcion' => 'Desparasitante de amplio espectro', 'precio_venta' => 80.00, 'stock_minimo' => 15.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 7, 'nombre' => 'Collar Antipulgas', 'tipo' => 'ACCESORIO', 'categoria' => 'ESTETICA', 'marca' => 'PetCare', 'unidad_medida' => 'Unidad', 'descripcion' => 'Collar preventivo contra pulgas', 'precio_venta' => 60.00, 'stock_minimo' => 10.00, 'control_lotes' => true, 'estado' => 'ACTIVO'],
            ['id_producto' => 8, 'nombre' => 'Juguete mordedor', 'tipo' => 'ACCESORIO', 'categoria' => 'ESTETICA', 'marca' => 'ProPet', 'unidad_medida' => 'Unidad', 'descripcion' => 'Juguete para entretenimiento', 'precio_venta' => 20.00, 'stock_minimo' => 5.00, 'control_lotes' => false, 'estado' => 'ACTIVO'],
        ]);

        DB::table('proveedores')->insert([
            ['id_proveedor' => 1, 'nombre' => 'VetLab S.A.', 'nit' => '123456789', 'telefono' => '71234577', 'email' => 'ventas@vetlab.com', 'direccion' => 'Av. Industrial N° 100', 'estado' => 'ACTIVO'],
            ['id_proveedor' => 2, 'nombre' => 'PetCare Bolivia', 'nit' => '987654321', 'telefono' => '71234578', 'email' => 'info@petcare.com', 'direccion' => 'Calle Comercio N° 200', 'estado' => 'ACTIVO'],
            ['id_proveedor' => 3, 'nombre' => 'ProPet Distribuciones', 'nit' => '456789123', 'telefono' => '71234579', 'email' => 'propet@distribuciones.com', 'direccion' => 'Av. Logistica N° 300', 'estado' => 'ACTIVO'],
            ['id_proveedor' => 4, 'nombre' => 'VetCenter Import', 'nit' => '789123456', 'telefono' => '71234580', 'email' => 'import@vetcenter.com', 'direccion' => 'Zona Franca N° 400', 'estado' => 'ACTIVO'],
        ]);

        DB::table('metodos_pago')->insert([
            ['id_metodo_pago' => 1, 'nombre' => 'EFECTIVO', 'estado' => 'ACTIVO'],
            ['id_metodo_pago' => 2, 'nombre' => 'TARJETA_CREDITO', 'estado' => 'ACTIVO'],
            ['id_metodo_pago' => 3, 'nombre' => 'TARJETA_DEBITO', 'estado' => 'ACTIVO'],
            ['id_metodo_pago' => 4, 'nombre' => 'QR', 'estado' => 'ACTIVO'],
        ]);
    }

    private function seedGestionPersonas(): void
    {
        DB::table('propietarios')->insert([
            ['id_persona' => 1],
            ['id_persona' => 2],
            ['id_persona' => 3],
            ['id_persona' => 4],
            ['id_persona' => 5],
        ]);

        DB::table('profesionales')->insert([
            ['id_persona' => 6, 'especialidad' => 'Veterinaria General', 'registro_profesional' => 'REG001', 'estado' => 'ACTIVO'],
            ['id_persona' => 7, 'especialidad' => 'Estetica Canina', 'registro_profesional' => 'REG002', 'estado' => 'ACTIVO'],
            ['id_persona' => 8, 'especialidad' => 'Veterinaria Especialista', 'registro_profesional' => 'REG003', 'estado' => 'ACTIVO'],
            ['id_persona' => 9, 'especialidad' => 'Estetica Felina', 'registro_profesional' => 'REG004', 'estado' => 'ACTIVO'],
        ]);

        DB::table('usuarios')->insert([
            ['id_usuario' => 1, 'id_persona' => 1, 'nombre_usuario' => 'jperez', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:30:00', 'ultimo_acceso' => '2026-01-15 08:30:00'],
            ['id_usuario' => 2, 'id_persona' => 2, 'nombre_usuario' => 'mlopez', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 09:00:00', 'ultimo_acceso' => '2026-01-15 09:00:00'],
            ['id_usuario' => 3, 'id_persona' => 6, 'nombre_usuario' => 'sgarcia', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-14 17:30:00', 'ultimo_acceso' => '2026-01-14 17:30:00'],
            ['id_usuario' => 4, 'id_persona' => 7, 'nombre_usuario' => 'pfernandez', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 07:45:00', 'ultimo_acceso' => '2026-01-15 07:45:00'],
            ['id_usuario' => 5, 'id_persona' => 5, 'nombre_usuario' => 'admin', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
            ['id_usuario' => 6, 'id_persona' => 3, 'nombre_usuario' => 'superadmin', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
            ['id_usuario' => 7, 'id_persona' => 11, 'nombre_usuario' => 'transporte', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
            ['id_usuario' => 8, 'id_persona' => 12, 'nombre_usuario' => 'inactivo', 'password_hash' => Hash::make('12345678'), 'estado' => 'INACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => null],
            ['id_usuario' => 9, 'id_persona' => 10, 'nombre_usuario' => 'recepcion', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
            ['id_usuario' => 10, 'id_persona' => 8, 'nombre_usuario' => 'veterinario', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
            ['id_usuario' => 11, 'id_persona' => 9, 'nombre_usuario' => 'spa', 'password_hash' => Hash::make('12345678'), 'estado' => 'ACTIVO', 'email_verified_at' => '2026-01-15 08:00:00', 'ultimo_acceso' => '2026-01-15 08:00:00'],
        ]);

        DB::table('usuario_rol')->insert([
            ['id_usuario' => 1, 'id_rol' => 1], // jperez -> ADMIN
            ['id_usuario' => 2, 'id_rol' => 4], // mlopez -> RECEPCION
            ['id_usuario' => 3, 'id_rol' => 2], // sgarcia -> VETERINARIO
            ['id_usuario' => 4, 'id_rol' => 3], // pfernandez -> ESTETICO
            ['id_usuario' => 5, 'id_rol' => 1], // admin -> ADMIN
            ['id_usuario' => 6, 'id_rol' => 5], // superadmin -> SUPER_ADMIN
            ['id_usuario' => 7, 'id_rol' => 6], // transporte -> TRANSPORTE
            ['id_usuario' => 8, 'id_rol' => 4], // inactivo -> RECEPCION
            ['id_usuario' => 9, 'id_rol' => 4], // recepcion -> RECEPCION
            ['id_usuario' => 10, 'id_rol' => 2], // veterinario -> VETERINARIO
            ['id_usuario' => 11, 'id_rol' => 3], // spa -> ESTETICO/SPA
        ]);

        DB::table('rol_permiso')->insert([
            ['id_rol' => 1, 'id_permiso' => 1],
            ['id_rol' => 1, 'id_permiso' => 2],
            ['id_rol' => 1, 'id_permiso' => 3],
            ['id_rol' => 1, 'id_permiso' => 4],
            ['id_rol' => 1, 'id_permiso' => 5],
            ['id_rol' => 1, 'id_permiso' => 6],
            ['id_rol' => 2, 'id_permiso' => 1],
            ['id_rol' => 2, 'id_permiso' => 2],
            ['id_rol' => 2, 'id_permiso' => 4],
            ['id_rol' => 3, 'id_permiso' => 1],
            ['id_rol' => 3, 'id_permiso' => 2],
            ['id_rol' => 4, 'id_permiso' => 1],
        ]);
    }

    private function seedPacientesAgenda(): void
    {
        DB::table('razas')->insert([
            ['id_raza' => 1, 'id_especie' => 1, 'nombre' => 'Pastor Aleman'],
            ['id_raza' => 2, 'id_especie' => 1, 'nombre' => 'Labrador'],
            ['id_raza' => 3, 'id_especie' => 1, 'nombre' => 'Bulldog'],
            ['id_raza' => 4, 'id_especie' => 2, 'nombre' => 'Persa'],
            ['id_raza' => 5, 'id_especie' => 2, 'nombre' => 'Siames'],
            ['id_raza' => 6, 'id_especie' => 3, 'nombre' => 'Holland Lop'],
            ['id_raza' => 7, 'id_especie' => 4, 'nombre' => 'Loro'],
        ]);

        DB::table('pacientes')->insert([
            ['id_paciente' => 1, 'id_raza' => 1, 'id_propietario' => 1, 'nombre' => 'Max', 'sexo' => 'MACHO', 'fecha_nacimiento' => '2020-05-10', 'tamanio' => 'GRANDE', 'condicion_reproductiva' => 'CASTRADO', 'color' => 'NEGRO', 'microchip' => '123456789', 'fecha_implantacion' => '2020-06-01', 'estado' => 'ACTIVO', 'caract_particulares' => 'Ninguna', 'tipo_pelaje' => 'CORTO', 'comportamiento_normal' => 'Juguetón', 'recomendaciones' => 'Ninguna', 'autorizacion_redes' => true],
            ['id_paciente' => 2, 'id_raza' => 4, 'id_propietario' => 2, 'nombre' => 'Luna', 'sexo' => 'HEMBRA', 'fecha_nacimiento' => '2021-08-15', 'tamanio' => 'PEQUEÑO', 'condicion_reproductiva' => 'INTACTO', 'color' => 'BLANCO', 'microchip' => '987654321', 'fecha_implantacion' => '2021-09-01', 'estado' => 'ACTIVO', 'caract_particulares' => 'Tímida', 'tipo_pelaje' => 'LARGO', 'comportamiento_normal' => 'Cariñosa', 'recomendaciones' => 'Ninguna', 'autorizacion_redes' => false],
            ['id_paciente' => 3, 'id_raza' => 2, 'id_propietario' => 3, 'nombre' => 'Rocky', 'sexo' => 'MACHO', 'fecha_nacimiento' => '2019-11-20', 'tamanio' => 'GRANDE', 'condicion_reproductiva' => 'CASTRADO', 'color' => 'DORADO', 'microchip' => '456789123', 'fecha_implantacion' => '2019-12-01', 'estado' => 'ACTIVO', 'caract_particulares' => 'Ninguna', 'tipo_pelaje' => 'CORTO', 'comportamiento_normal' => 'Energético', 'recomendaciones' => 'Ninguna', 'autorizacion_redes' => true],
            ['id_paciente' => 4, 'id_raza' => 6, 'id_propietario' => 4, 'nombre' => 'Nieve', 'sexo' => 'HEMBRA', 'fecha_nacimiento' => '2022-02-14', 'tamanio' => 'PEQUEÑO', 'condicion_reproductiva' => 'INTACTO', 'color' => 'BLANCO', 'microchip' => '789123456', 'fecha_implantacion' => '2022-03-01', 'estado' => 'ACTIVO', 'caract_particulares' => 'Ninguna', 'tipo_pelaje' => 'CORTO', 'comportamiento_normal' => 'Tranquila', 'recomendaciones' => 'Ninguna', 'autorizacion_redes' => false],
            ['id_paciente' => 5, 'id_raza' => 7, 'id_propietario' => 5, 'nombre' => 'Rio', 'sexo' => 'MACHO', 'fecha_nacimiento' => '2021-06-30', 'tamanio' => 'PEQUEÑO', 'condicion_reproductiva' => 'INTACTO', 'color' => 'VERDE', 'microchip' => '321654987', 'fecha_implantacion' => '2021-07-01', 'estado' => 'ACTIVO', 'caract_particulares' => 'Ninguna', 'tipo_pelaje' => 'PLUMA', 'comportamiento_normal' => 'Canta mucho', 'recomendaciones' => 'Ninguna', 'autorizacion_redes' => false],
        ]);

        DB::table('tiempos_estimados')->insert([
            ['id_tiempo_estimado' => 1, 'id_servicio' => 1, 'id_especie' => 1, 'peso_min' => 0.00, 'peso_max' => 10.00, 'duracion_minutos' => 30],
            ['id_tiempo_estimado' => 2, 'id_servicio' => 1, 'id_especie' => 1, 'peso_min' => 10.01, 'peso_max' => 25.00, 'duracion_minutos' => 45],
            ['id_tiempo_estimado' => 3, 'id_servicio' => 1, 'id_especie' => 1, 'peso_min' => 25.01, 'peso_max' => 100.00, 'duracion_minutos' => 60],
            ['id_tiempo_estimado' => 4, 'id_servicio' => 2, 'id_especie' => 1, 'peso_min' => 0.00, 'peso_max' => 10.00, 'duracion_minutos' => 20],
            ['id_tiempo_estimado' => 5, 'id_servicio' => 2, 'id_especie' => 1, 'peso_min' => 10.01, 'peso_max' => 25.00, 'duracion_minutos' => 35],
            ['id_tiempo_estimado' => 6, 'id_servicio' => 2, 'id_especie' => 1, 'peso_min' => 25.01, 'peso_max' => 100.00, 'duracion_minutos' => 50],
            ['id_tiempo_estimado' => 7, 'id_servicio' => 3, 'id_especie' => 1, 'peso_min' => 0.00, 'peso_max' => 100.00, 'duracion_minutos' => 15],
            ['id_tiempo_estimado' => 8, 'id_servicio' => 4, 'id_especie' => 1, 'peso_min' => 0.00, 'peso_max' => 100.00, 'duracion_minutos' => 30],
            ['id_tiempo_estimado' => 9, 'id_servicio' => 4, 'id_especie' => 2, 'peso_min' => 0.00, 'peso_max' => 100.00, 'duracion_minutos' => 30],
        ]);

        DB::table('profesional_servicio')->insert([
            ['id_profesional' => 6, 'id_servicio' => 4, 'estado' => 'ACTIVO'],
            ['id_profesional' => 6, 'id_servicio' => 5, 'estado' => 'ACTIVO'],
            ['id_profesional' => 6, 'id_servicio' => 6, 'estado' => 'ACTIVO'],
            ['id_profesional' => 7, 'id_servicio' => 1, 'estado' => 'ACTIVO'],
            ['id_profesional' => 7, 'id_servicio' => 2, 'estado' => 'ACTIVO'],
            ['id_profesional' => 7, 'id_servicio' => 3, 'estado' => 'ACTIVO'],
            ['id_profesional' => 8, 'id_servicio' => 4, 'estado' => 'ACTIVO'],
            ['id_profesional' => 8, 'id_servicio' => 5, 'estado' => 'ACTIVO'],
            ['id_profesional' => 9, 'id_servicio' => 1, 'estado' => 'ACTIVO'],
            ['id_profesional' => 9, 'id_servicio' => 2, 'estado' => 'ACTIVO'],
        ]);

        $horarios = [];
        foreach ([6 => ['08:00:00', '17:00:00'], 7 => ['09:00:00', '18:00:00'], 8 => ['08:00:00', '16:00:00']] as $profesional => [$inicio, $fin]) {
            foreach (range(1, 5) as $dia) {
                $horarios[] = ['id_profesional' => $profesional, 'dia_semana' => $dia, 'hora_inicio' => $inicio, 'hora_fin' => $fin, 'estado' => 'ACTIVO'];
            }
        }
        DB::table('horario_profesionales')->insert($horarios);

        DB::table('bloqueo_agendas')->insert([
            ['id_bloqueo' => 1, 'id_profesional' => 6, 'fecha_hora_inicio' => '2026-01-20 08:00:00', 'fecha_hora_fin' => '2026-01-20 12:00:00', 'motivo' => 'Capacitacion'],
            ['id_bloqueo' => 2, 'id_profesional' => 6, 'fecha_hora_inicio' => '2026-01-25 14:00:00', 'fecha_hora_fin' => '2026-01-25 17:00:00', 'motivo' => 'Reunion'],
            ['id_bloqueo' => 3, 'id_profesional' => 7, 'fecha_hora_inicio' => '2026-01-22 09:00:00', 'fecha_hora_fin' => '2026-01-22 11:00:00', 'motivo' => 'Vacaciones'],
            ['id_bloqueo' => 4, 'id_profesional' => 8, 'fecha_hora_inicio' => '2026-01-28 08:00:00', 'fecha_hora_fin' => '2026-01-28 16:00:00', 'motivo' => 'Dia libre'],
        ]);
    }

    private function seedCitas(): void
    {
        DB::table('citas')->insert([
            ['id_cita' => 1, 'id_paciente' => 1, 'id_profesional' => 6, 'fecha_hora_inicio' => '2026-01-20 09:00:00', 'fecha_hora_fin' => '2026-01-20 09:30:00', 'estado' => 'CONFIRMADA', 'motivo_cancelacion' => null, 'origen' => 'WEB'],
            ['id_cita' => 2, 'id_paciente' => 2, 'id_profesional' => 7, 'fecha_hora_inicio' => '2026-01-20 10:00:00', 'fecha_hora_fin' => '2026-01-20 10:45:00', 'estado' => 'CONFIRMADA', 'motivo_cancelacion' => null, 'origen' => 'TELEFONO'],
            ['id_cita' => 3, 'id_paciente' => 3, 'id_profesional' => 6, 'fecha_hora_inicio' => '2026-01-21 14:00:00', 'fecha_hora_fin' => '2026-01-21 14:30:00', 'estado' => 'PENDIENTE', 'motivo_cancelacion' => null, 'origen' => 'WEB'],
            ['id_cita' => 4, 'id_paciente' => 4, 'id_profesional' => 7, 'fecha_hora_inicio' => '2026-01-22 11:00:00', 'fecha_hora_fin' => '2026-01-22 11:30:00', 'estado' => 'CANCELADA', 'motivo_cancelacion' => 'Dueño no confirmo', 'origen' => 'TELEFONO'],
            ['id_cita' => 5, 'id_paciente' => 5, 'id_profesional' => 8, 'fecha_hora_inicio' => '2026-01-23 15:00:00', 'fecha_hora_fin' => '2026-01-23 15:30:00', 'estado' => 'CONFIRMADA', 'motivo_cancelacion' => null, 'origen' => 'WEB'],
        ]);

        DB::table('detalle_citas')->insert([
            ['id_detalle_cita' => 1, 'id_cita' => 1, 'id_servicio' => 4, 'costo_estimado' => 100.00, 'tiempo_estimado_minutos' => 30],
            ['id_detalle_cita' => 2, 'id_cita' => 2, 'id_servicio' => 1, 'costo_estimado' => 50.00, 'tiempo_estimado_minutos' => 30],
            ['id_detalle_cita' => 3, 'id_cita' => 2, 'id_servicio' => 2, 'costo_estimado' => 80.00, 'tiempo_estimado_minutos' => 35],
            ['id_detalle_cita' => 4, 'id_cita' => 3, 'id_servicio' => 5, 'costo_estimado' => 150.00, 'tiempo_estimado_minutos' => 30],
            ['id_detalle_cita' => 5, 'id_cita' => 4, 'id_servicio' => 1, 'costo_estimado' => 50.00, 'tiempo_estimado_minutos' => 30],
            ['id_detalle_cita' => 6, 'id_cita' => 5, 'id_servicio' => 6, 'costo_estimado' => 120.00, 'tiempo_estimado_minutos' => 30],
        ]);

        DB::table('servicio_domicilios')->insert([
            ['id_servicio_domicilio' => 1, 'id_cita' => 2, 'direccion' => 'Calle Sucre N° 456', 'referencia' => 'Cerca al mercado', 'distancia_estimada' => 2.50, 'costo_domicilio' => 20.00, 'estado' => 'PENDIENTE'],
            ['id_servicio_domicilio' => 2, 'id_cita' => 3, 'direccion' => 'Av. America N° 789', 'referencia' => 'Edificio Azul', 'distancia_estimada' => 5.00, 'costo_domicilio' => 30.00, 'estado' => 'PENDIENTE'],
        ]);
    }

    private function seedAtenciones(): void
    {
        DB::table('atenciones')->insert([
            ['id_atencion' => 1, 'id_paciente' => 1, 'id_cita' => 1, 'fecha_hora_inicio' => '2026-01-20 09:00:00', 'fecha_hora_fin' => '2026-01-20 09:30:00', 'estado' => 'COMPLETADA', 'observaciones' => 'Paciente tranquilo', 'recomendaciones' => 'Control en 3 meses', 'total_final' => 100.00],
            ['id_atencion' => 2, 'id_paciente' => 2, 'id_cita' => 2, 'fecha_hora_inicio' => '2026-01-20 10:00:00', 'fecha_hora_fin' => '2026-01-20 10:45:00', 'estado' => 'COMPLETADA', 'observaciones' => 'Pelo en buenas condiciones', 'recomendaciones' => 'Seguir con acondicionador', 'total_final' => 130.00],
            ['id_atencion' => 3, 'id_paciente' => 3, 'id_cita' => 3, 'fecha_hora_inicio' => '2026-01-21 14:00:00', 'fecha_hora_fin' => null, 'estado' => 'PENDIENTE', 'observaciones' => null, 'recomendaciones' => null, 'total_final' => null],
        ]);

        DB::table('atencion_veterinarias')->insert([
            ['id_atencion' => 1, 'motivo_consulta' => 'Control general', 'tipo' => 'CONSULTA', 'anamnesis' => 'Paciente sano', 'sintomas' => 'Sin sintomas', 'examen_fisico' => 'Normal', 'conclusion' => 'Todo bien', 'fecha_reconsulta' => '2026-04-20 09:00:00', 'observaciones_clinicas' => 'Ninguna'],
        ]);

        DB::table('atencion_esteticas')->insert([
            ['id_atencion' => 2, 'modalidad' => 'BAÑO_CORTE', 'estado_pelaje' => 'BUENO', 'caracteristicas_observadas' => 'Pelo sedoso sin nudos'],
        ]);

        DB::table('participacion_atencions')->insert([
            ['id_participacion' => 1, 'id_atencion' => 1, 'id_profesional' => 6, 'rol_atencion' => 'VETERINARIO', 'observaciones' => 'Atencion principal'],
            ['id_participacion' => 2, 'id_atencion' => 2, 'id_profesional' => 7, 'rol_atencion' => 'ESTETICO', 'observaciones' => 'Atencion principal'],
        ]);

        DB::table('atencion_servicios')->insert([
            ['id_atencion_servicio' => 1, 'id_atencion' => 1, 'id_servicio' => 4, 'cantidad' => 1, 'precio_unitario' => 100.00, 'descuento' => 0.00],
            ['id_atencion_servicio' => 2, 'id_atencion' => 2, 'id_servicio' => 1, 'cantidad' => 1, 'precio_unitario' => 50.00, 'descuento' => 0.00],
            ['id_atencion_servicio' => 3, 'id_atencion' => 2, 'id_servicio' => 2, 'cantidad' => 1, 'precio_unitario' => 80.00, 'descuento' => 0.00],
        ]);

        DB::table('ficha_medicas')->insert([
            ['id_ficha' => 1, 'id_paciente' => 1, 'fecha_apertura' => '2020-06-01', 'antecedentes' => 'Ninguno', 'alergias' => 'Ninguna', 'enfermedades_previas' => 'Ninguna', 'cirugias_previas' => 'Castracion 2021', 'medicamentos_habituales' => 'Ninguno', 'observaciones' => 'Paciente sano'],
            ['id_ficha' => 2, 'id_paciente' => 2, 'fecha_apertura' => '2021-09-01', 'antecedentes' => 'Ninguno', 'alergias' => 'Ninguna', 'enfermedades_previas' => 'Ninguna', 'cirugias_previas' => 'Ninguna', 'medicamentos_habituales' => 'Ninguno', 'observaciones' => 'Paciente sano'],
            ['id_ficha' => 3, 'id_paciente' => 3, 'fecha_apertura' => '2019-12-01', 'antecedentes' => 'Ninguno', 'alergias' => 'Ninguna', 'enfermedades_previas' => 'Ninguna', 'cirugias_previas' => 'Castracion 2020', 'medicamentos_habituales' => 'Ninguno', 'observaciones' => 'Paciente sano'],
        ]);
    }

    private function seedHistorialClinico(): void
    {
        DB::table('registro_signos_vitales')->insert([
            ['id_registro_signos' => 1, 'id_atencion' => 1, 'fecha_hora' => '2026-01-20 09:00:00', 'peso' => 25.00, 'temperatura' => 38.50, 'frecuencia_cardiaca' => 80, 'frecuencia_respiratoria' => 20],
            ['id_registro_signos' => 2, 'id_atencion' => 1, 'fecha_hora' => '2026-01-20 09:15:00', 'peso' => 25.00, 'temperatura' => 38.60, 'frecuencia_cardiaca' => 75, 'frecuencia_respiratoria' => 18],
        ]);

        DB::table('diagnosticos')->insert([
            ['id_diagnostico' => 1, 'id_atencion' => 1, 'nombre' => 'Sano', 'descripcion' => 'Paciente sin patologias', 'tipo' => 'PREVENTIVO', 'observaciones' => 'Ninguna'],
        ]);

        DB::table('tratamientos')->insert([
            ['id_tratamiento' => 1, 'id_paciente' => 1, 'descripcion' => 'Control anual', 'indicaciones' => 'Vacunacion y desparasitacion', 'fecha_inicio' => '2026-01-20', 'fecha_fin' => '2026-12-20', 'estado' => 'ACTIVO', 'observaciones' => 'Seguimiento anual'],
            ['id_tratamiento' => 2, 'id_paciente' => 3, 'descripcion' => 'Desparasitacion', 'indicaciones' => 'Aplicar cada 3 meses', 'fecha_inicio' => '2026-01-15', 'fecha_fin' => '2026-04-15', 'estado' => 'ACTIVO', 'observaciones' => 'Desparasitacion interna'],
        ]);

        DB::table('atencion_tratamiento')->insert([
            ['id_atencion' => 1, 'id_tratamiento' => 1, 'tipo_relacion' => 'INICIO'],
        ]);
    }

    private function seedMedicamentos(): void
    {
        DB::table('medicamentos')->insert([
            ['id_producto' => 4, 'principio_activo' => 'Virus Rabia', 'presentacion' => 'Inyectable', 'concentracion' => 1.00, 'unidad_concentracion' => 'ML'],
            ['id_producto' => 5, 'principio_activo' => 'Virus Panleucopenia', 'presentacion' => 'Inyectable', 'concentracion' => 1.00, 'unidad_concentracion' => 'ML'],
            ['id_producto' => 6, 'principio_activo' => 'Praziquantel', 'presentacion' => 'Tableta', 'concentracion' => 50.00, 'unidad_concentracion' => 'MG'],
        ]);

        DB::table('uso_producto_atencions')->insert([
            ['id_uso' => 1, 'id_atencion' => 1, 'id_producto' => 4, 'id_lote' => null, 'cantidad' => 1.00, 'observaciones' => 'Vacuna aplicada'],
            ['id_uso' => 2, 'id_atencion' => 1, 'id_producto' => 6, 'id_lote' => null, 'cantidad' => 1.00, 'observaciones' => 'Desparasitante aplicado'],
            ['id_uso' => 3, 'id_atencion' => 2, 'id_producto' => 1, 'id_lote' => null, 'cantidad' => 0.50, 'observaciones' => 'Shampoo utilizado'],
            ['id_uso' => 4, 'id_atencion' => 2, 'id_producto' => 2, 'id_lote' => null, 'cantidad' => 0.30, 'observaciones' => 'Acondicionador utilizado'],
        ]);

        DB::table('administracion_medicamentos')->insert([
            ['id_uso' => 1, 'fecha_hora' => '2026-01-20 09:10:00', 'dosis' => 1.00, 'unidad_dosis' => 'ML', 'via_administracion' => 'SUBCUTANEA', 'observaciones_clinicas' => 'Sin reacciones'],
            ['id_uso' => 2, 'fecha_hora' => '2026-01-20 09:15:00', 'dosis' => 1.00, 'unidad_dosis' => 'TABLE', 'via_administracion' => 'ORAL', 'observaciones_clinicas' => 'Paciente tolero bien'],
        ]);

        DB::table('recetas')->insert([
            ['id_receta' => 1, 'id_atencion' => 1, 'fecha_hora' => '2026-01-20 09:20:00', 'indicaciones_generales' => 'Tomar medicamento cada 8 horas'],
        ]);

        DB::table('detalle_recetas')->insert([
            ['id_detalle_receta' => 1, 'id_receta' => 1, 'id_producto' => 4, 'dosis' => 1.00, 'unidad_dosis' => 'ML', 'frecuencia' => 'Cada 24 horas', 'duracion' => '5 dias', 'via_administracion' => 'SUBCUTANEA', 'indicaciones' => 'Aplicar en la nuca'],
            ['id_detalle_receta' => 2, 'id_receta' => 1, 'id_producto' => 6, 'dosis' => 1.00, 'unidad_dosis' => 'TABLE', 'frecuencia' => 'Cada 8 horas', 'duracion' => '7 dias', 'via_administracion' => 'ORAL', 'indicaciones' => 'Tomar con agua'],
        ]);
    }

    private function seedCompras(): void
    {
        DB::table('compras')->insert([
            ['id_compra' => 1, 'id_proveedor' => 1, 'fecha_hora' => '2026-01-10 10:00:00', 'estado' => 'RECIBIDA', 'total' => 1500.00, 'observaciones' => 'Compra mensual'],
            ['id_compra' => 2, 'id_proveedor' => 2, 'fecha_hora' => '2026-01-12 14:00:00', 'estado' => 'RECIBIDA', 'total' => 800.00, 'observaciones' => 'Insumos de estetica'],
        ]);

        DB::table('detalle_compras')->insert([
            ['id_detalle_compra' => 1, 'id_compra' => 1, 'id_producto' => 4, 'cantidad' => 10.00, 'precio_unitario' => 100.00],
            ['id_detalle_compra' => 2, 'id_compra' => 1, 'id_producto' => 5, 'cantidad' => 8.00, 'precio_unitario' => 120.00],
            ['id_detalle_compra' => 3, 'id_compra' => 1, 'id_producto' => 6, 'cantidad' => 15.00, 'precio_unitario' => 60.00],
            ['id_detalle_compra' => 4, 'id_compra' => 2, 'id_producto' => 1, 'cantidad' => 5.00, 'precio_unitario' => 35.00],
            ['id_detalle_compra' => 5, 'id_compra' => 2, 'id_producto' => 2, 'cantidad' => 5.00, 'precio_unitario' => 25.00],
            ['id_detalle_compra' => 6, 'id_compra' => 2, 'id_producto' => 7, 'cantidad' => 10.00, 'precio_unitario' => 50.00],
        ]);

        DB::table('lotes')->insert([
            ['id_lote' => 1, 'id_producto' => 4, 'id_detalle_compra' => 1, 'numero_lote' => 'LOT-001-2026', 'fecha_vencimiento' => '2027-01-10', 'estado' => 'ACTIVO'],
            ['id_lote' => 2, 'id_producto' => 5, 'id_detalle_compra' => 2, 'numero_lote' => 'LOT-002-2026', 'fecha_vencimiento' => '2027-01-12', 'estado' => 'ACTIVO'],
            ['id_lote' => 3, 'id_producto' => 6, 'id_detalle_compra' => 3, 'numero_lote' => 'LOT-003-2026', 'fecha_vencimiento' => '2027-01-10', 'estado' => 'ACTIVO'],
            ['id_lote' => 4, 'id_producto' => 1, 'id_detalle_compra' => 4, 'numero_lote' => 'LOT-004-2026', 'fecha_vencimiento' => '2027-01-12', 'estado' => 'ACTIVO'],
        ]);
    }

    private function seedFacturacion(): void
    {
        DB::table('cuentas')->insert([
            ['id_cuenta' => 1, 'id_propietario' => 1, 'fecha_apertura' => '2026-01-20', 'estado' => 'ACTIVO'],
            ['id_cuenta' => 2, 'id_propietario' => 2, 'fecha_apertura' => '2026-01-20', 'estado' => 'ACTIVO'],
            ['id_cuenta' => 3, 'id_propietario' => 3, 'fecha_apertura' => '2026-01-21', 'estado' => 'ACTIVO'],
            ['id_cuenta' => 4, 'id_propietario' => 4, 'fecha_apertura' => '2026-01-22', 'estado' => 'ACTIVO'],
            ['id_cuenta' => 5, 'id_propietario' => 5, 'fecha_apertura' => '2026-01-23', 'estado' => 'ACTIVO'],
        ]);

        DB::table('cargos')->insert([
            ['id_cargo' => 1, 'id_cuenta' => 1, 'id_tratamiento' => 1, 'fecha_hora' => '2026-01-20 09:30:00', 'concepto' => 'Consulta Veterinaria', 'monto' => 100.00, 'descuento' => 0.00, 'estado' => 'PAGADO', 'observaciones' => null],
            ['id_cargo' => 2, 'id_cuenta' => 2, 'id_tratamiento' => null, 'fecha_hora' => '2026-01-20 10:45:00', 'concepto' => 'Baño y Corte', 'monto' => 130.00, 'descuento' => 0.00, 'estado' => 'PENDIENTE', 'observaciones' => null],
            ['id_cargo' => 3, 'id_cuenta' => 3, 'id_tratamiento' => 2, 'fecha_hora' => '2026-01-21 14:30:00', 'concepto' => 'Desparasitacion', 'monto' => 150.00, 'descuento' => 10.00, 'estado' => 'PENDIENTE', 'observaciones' => 'Descuento por promocion'],
        ]);

        DB::table('cierre_cajas')->insert([
            ['id_cierre' => 1, 'id_usuario' => 1, 'fecha_hora_apertura' => '2026-01-20 08:00:00', 'fecha_hora_cierre' => '2026-01-20 18:00:00', 'total_registrado' => 230.00, 'observaciones' => 'Cierre diario'],
        ]);

        DB::table('pagos')->insert([
            ['id_pago' => 1, 'id_cuenta' => 1, 'id_cierre' => 1, 'fecha_hora' => '2026-01-20 09:30:00', 'estado' => 'COMPLETADO', 'observaciones' => 'Pago en efectivo'],
            ['id_pago' => 2, 'id_cuenta' => 2, 'id_cierre' => 1, 'fecha_hora' => '2026-01-20 10:45:00', 'estado' => 'COMPLETADO', 'observaciones' => 'Pago con tarjeta'],
        ]);

        DB::table('detalle_pagos')->insert([
            ['id_detalle_pago' => 1, 'id_pago' => 1, 'id_metodo_pago' => 1, 'monto' => 100.00, 'referencia' => null],
            ['id_detalle_pago' => 2, 'id_pago' => 2, 'id_metodo_pago' => 2, 'monto' => 130.00, 'referencia' => 'REF-12345'],
        ]);

        DB::table('aplicacion_pagos')->insert([
            ['id_aplicacion' => 1, 'id_pago' => 1, 'id_cargo' => 1, 'monto_aplicado' => 100.00],
        ]);

        DB::table('pago_atencions')->insert([
            ['id_pago_atencion' => 1, 'id_pago' => 1, 'id_atencion' => 1, 'monto_aplicado' => 100.00],
            ['id_pago_atencion' => 2, 'id_pago' => 2, 'id_atencion' => 2, 'monto_aplicado' => 130.00],
        ]);
    }

    private function seedVentasInventario(): void
    {
        DB::table('ventas')->insert([
            ['id_venta' => 1, 'id_propietario' => 1, 'id_usuario' => 1, 'fecha_hora' => '2026-01-20 11:00:00', 'estado' => 'CONFIRMADA', 'total' => 80.00, 'observaciones' => 'Venta de productos'],
            ['id_venta' => 2, 'id_propietario' => 2, 'id_usuario' => 2, 'fecha_hora' => '2026-01-20 15:00:00', 'estado' => 'CONFIRMADA', 'total' => 120.00, 'observaciones' => 'Venta de productos'],
        ]);

        DB::table('detalle_ventas')->insert([
            ['id_detalle_venta' => 1, 'id_venta' => 1, 'id_producto' => 7, 'cantidad' => 1.00, 'precio_unitario' => 60.00, 'descuento' => 0.00],
            ['id_detalle_venta' => 2, 'id_venta' => 1, 'id_producto' => 8, 'cantidad' => 1.00, 'precio_unitario' => 20.00, 'descuento' => 0.00],
            ['id_detalle_venta' => 3, 'id_venta' => 2, 'id_producto' => 1, 'cantidad' => 2.00, 'precio_unitario' => 45.00, 'descuento' => 0.00],
            ['id_detalle_venta' => 4, 'id_venta' => 2, 'id_producto' => 2, 'cantidad' => 1.00, 'precio_unitario' => 30.00, 'descuento' => 0.00],
        ]);

        DB::table('movimiento_inventarios')->insert([
            ['id_movimiento' => 1, 'id_producto' => 4, 'id_lote' => 1, 'id_usuario' => 1, 'id_detalle_compra' => 1, 'id_uso' => null, 'id_detalle_venta' => null, 'fecha_hora' => '2026-01-10 10:00:00', 'tipo_movimiento' => 'ENTRADA', 'cantidad' => 10.00, 'motivo' => 'COMPRA'],
            ['id_movimiento' => 2, 'id_producto' => 5, 'id_lote' => 2, 'id_usuario' => 1, 'id_detalle_compra' => 2, 'id_uso' => null, 'id_detalle_venta' => null, 'fecha_hora' => '2026-01-10 10:00:00', 'tipo_movimiento' => 'ENTRADA', 'cantidad' => 8.00, 'motivo' => 'COMPRA'],
            ['id_movimiento' => 3, 'id_producto' => 6, 'id_lote' => 3, 'id_usuario' => 1, 'id_detalle_compra' => 3, 'id_uso' => null, 'id_detalle_venta' => null, 'fecha_hora' => '2026-01-10 10:00:00', 'tipo_movimiento' => 'ENTRADA', 'cantidad' => 15.00, 'motivo' => 'COMPRA'],
            ['id_movimiento' => 4, 'id_producto' => 4, 'id_lote' => 1, 'id_usuario' => 3, 'id_detalle_compra' => null, 'id_uso' => 1, 'id_detalle_venta' => null, 'fecha_hora' => '2026-01-20 09:10:00', 'tipo_movimiento' => 'SALIDA', 'cantidad' => -1.00, 'motivo' => 'USO_ATENCION'],
            ['id_movimiento' => 5, 'id_producto' => 6, 'id_lote' => 3, 'id_usuario' => 3, 'id_detalle_compra' => null, 'id_uso' => 2, 'id_detalle_venta' => null, 'fecha_hora' => '2026-01-20 09:15:00', 'tipo_movimiento' => 'SALIDA', 'cantidad' => -1.00, 'motivo' => 'USO_ATENCION'],
            ['id_movimiento' => 6, 'id_producto' => 7, 'id_lote' => null, 'id_usuario' => 1, 'id_detalle_compra' => null, 'id_uso' => null, 'id_detalle_venta' => 1, 'fecha_hora' => '2026-01-20 11:00:00', 'tipo_movimiento' => 'SALIDA', 'cantidad' => -1.00, 'motivo' => 'VENTA'],
        ]);
    }

    private function seedPaquetes(): void
    {
        DB::table('paquetes')->insert([
            ['id_paquete' => 1, 'nombre' => 'Pack Premium', 'descripcion' => 'Baño + Corte + Uñas', 'precio' => 150.00, 'estado' => 'ACTIVO'],
            ['id_paquete' => 2, 'nombre' => 'Pack Basico', 'descripcion' => 'Baño + Uñas', 'precio' => 70.00, 'estado' => 'ACTIVO'],
        ]);

        DB::table('detalle_paquetes')->insert([
            ['id_detalle_paquete' => 1, 'id_paquete' => 1, 'id_servicio' => 1, 'cantidad_incluida' => 1],
            ['id_detalle_paquete' => 2, 'id_paquete' => 1, 'id_servicio' => 2, 'cantidad_incluida' => 1],
            ['id_detalle_paquete' => 3, 'id_paquete' => 1, 'id_servicio' => 3, 'cantidad_incluida' => 1],
            ['id_detalle_paquete' => 4, 'id_paquete' => 2, 'id_servicio' => 1, 'cantidad_incluida' => 1],
            ['id_detalle_paquete' => 5, 'id_paquete' => 2, 'id_servicio' => 3, 'cantidad_incluida' => 1],
        ]);

        DB::table('suscripciones')->insert([
            ['id_suscripcion' => 1, 'id_paciente' => 1, 'id_paquete' => 1, 'fecha_inicio' => '2026-01-20', 'fecha_fin' => '2026-07-20', 'estado' => 'ACTIVO'],
            ['id_suscripcion' => 2, 'id_paciente' => 2, 'id_paquete' => 2, 'fecha_inicio' => '2026-01-20', 'fecha_fin' => '2026-07-20', 'estado' => 'ACTIVO'],
        ]);

        DB::table('recordatorios')->insert([
            ['id_recordatorio' => 1, 'id_paciente' => 1, 'id_servicio' => 5, 'id_atencion_origen' => null, 'id_cita' => null, 'tipo' => 'VACUNACION', 'fecha_hora_programada' => '2026-04-20 08:00:00', 'fecha_hora_envio' => null, 'medio_envio' => 'EMAIL', 'mensaje' => 'Recordatorio de vacunacion para Max', 'estado' => 'PENDIENTE'],
            ['id_recordatorio' => 2, 'id_paciente' => 1, 'id_servicio' => 4, 'id_atencion_origen' => 1, 'id_cita' => null, 'tipo' => 'CONTROL', 'fecha_hora_programada' => '2026-04-20 09:00:00', 'fecha_hora_envio' => null, 'medio_envio' => 'SMS', 'mensaje' => 'Recordatorio de control para Max', 'estado' => 'PENDIENTE'],
            ['id_recordatorio' => 3, 'id_paciente' => 2, 'id_servicio' => 1, 'id_atencion_origen' => null, 'id_cita' => 2, 'tipo' => 'CITA', 'fecha_hora_programada' => '2026-01-19 10:00:00', 'fecha_hora_envio' => '2026-01-19 08:00:00', 'medio_envio' => 'EMAIL', 'mensaje' => 'Recordatorio de cita para Luna', 'estado' => 'ENVIADO'],
        ]);
    }

    private function resetSequence(): void
    {
        $tables = [
            'personas' => 'id_persona',
            'roles' => 'id_rol',
            'permisos' => 'id_permiso',
            'especies' => 'id_especie',
            'servicios' => 'id_servicio',
            'productos' => 'id_producto',
            'proveedores' => 'id_proveedor',
            'metodos_pago' => 'id_metodo_pago',
            'usuarios' => 'id_usuario',
            'razas' => 'id_raza',
            'pacientes' => 'id_paciente',
            'tiempos_estimados' => 'id_tiempo_estimado',
            'horario_profesionales' => 'id_horario',
            'bloqueo_agendas' => 'id_bloqueo',
            'citas' => 'id_cita',
            'detalle_citas' => 'id_detalle_cita',
            'servicio_domicilios' => 'id_servicio_domicilio',
            'atenciones' => 'id_atencion',
            'participacion_atencions' => 'id_participacion',
            'atencion_servicios' => 'id_atencion_servicio',
            'ficha_medicas' => 'id_ficha',
            'registro_signos_vitales' => 'id_registro_signos',
            'diagnosticos' => 'id_diagnostico',
            'tratamientos' => 'id_tratamiento',
            'uso_producto_atencions' => 'id_uso',
            'recetas' => 'id_receta',
            'detalle_recetas' => 'id_detalle_receta',
            'compras' => 'id_compra',
            'detalle_compras' => 'id_detalle_compra',
            'lotes' => 'id_lote',
            'cuentas' => 'id_cuenta',
            'cargos' => 'id_cargo',
            'cierre_cajas' => 'id_cierre',
            'pagos' => 'id_pago',
            'detalle_pagos' => 'id_detalle_pago',
            'aplicacion_pagos' => 'id_aplicacion',
            'pago_atencions' => 'id_pago_atencion',
            'ventas' => 'id_venta',
            'detalle_ventas' => 'id_detalle_venta',
            'movimiento_inventarios' => 'id_movimiento',
            'paquetes' => 'id_paquete',
            'detalle_paquetes' => 'id_detalle_paquete',
            'suscripciones' => 'id_suscripcion',
            'recordatorios' => 'id_recordatorio',
            'registro_accesos' => 'id_acceso',
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table)) {
                DB::statement(<<<SQL
                    SELECT setval(pg_get_serial_sequence('{$table}', '{$column}'), COALESCE((SELECT MAX({$column}) FROM {$table}), 1))
                SQL);
            }
        }
    }

    private function seedRegistroAccesos(): void
    {
        if (Schema::hasTable('registro_accesos')) {
            DB::table('registro_accesos')->insert([
                [
                    'id_acceso' => 1,
                    'id_usuario' => 6,
                    'nombre_usuario' => 'superadmin',
                    'rol' => 'SUPER_ADMIN',
                    'fecha_ingreso' => now()->toDateString(),
                    'hora_ingreso' => '08:00:00',
                    'fecha_hora_cierre' => null,
                    'estado' => 'ACTIVA',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_acceso' => 2,
                    'id_usuario' => 5,
                    'nombre_usuario' => 'admin',
                    'rol' => 'ADMIN',
                    'fecha_ingreso' => now()->toDateString(),
                    'hora_ingreso' => '08:15:00',
                    'fecha_hora_cierre' => now()->subMinutes(15),
                    'estado' => 'CERRADA',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
                    'created_at' => now()->subHour(),
                    'updated_at' => now()->subMinutes(15),
                ],
                [
                    'id_acceso' => 3,
                    'id_usuario' => 3,
                    'nombre_usuario' => 'veterinario',
                    'rol' => 'VETERINARIO',
                    'fecha_ingreso' => now()->toDateString(),
                    'hora_ingreso' => '08:30:00',
                    'fecha_hora_cierre' => null,
                    'estado' => 'ACTIVA',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/122.0',
                    'created_at' => now()->subMinutes(30),
                    'updated_at' => now()->subMinutes(30),
                ],
            ]);
        }
    }
}
