-- ==============================================================================
-- ODIN - GESTOR DOCUMENTAL
-- Script DDL de Estructura: Tabla de Consecutivos Anuales de Radicación
-- Motor: PostgreSQL 14+ / Supabase (Base de Datos Remota / Nube)
-- ==============================================================================

-- 1. Crear tabla de control concurrente de consecutivos por vigencia y tipo
CREATE TABLE IF NOT EXISTS public.radicado_consecutivos (
    anio INTEGER NOT NULL,
    prefijo VARCHAR(5) NOT NULL,
    consecutivo INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT pk_radicado_consecutivos PRIMARY KEY (anio, prefijo),
    CONSTRAINT chk_consecutivos_positivos CHECK (consecutivo >= 0)
);

COMMENT ON TABLE public.radicado_consecutivos IS 'Control atomico de consecutivos de radicacion por vigencia anual (RF-05, RF-06)';
COMMENT ON COLUMN public.radicado_consecutivos.anio IS 'Vigencia fiscal anual (ej. 2026)';
COMMENT ON COLUMN public.radicado_consecutivos.prefijo IS 'Prefijo de radicacion: E (Entrada), S (Salida), INT (Interno)';
COMMENT ON COLUMN public.radicado_consecutivos.consecutivo IS 'Ultimo consecutivo generado para la vigencia y prefijo';

-- 2. Inicializar consecutivo con el maximo existente en radicados historicos (si aplica)
INSERT INTO public.radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER AS anio,
    'E' AS prefijo,
    COALESCE(MAX(NULLIF(regexp_replace(numero_radicado, '^.*-', ''), '')::INTEGER), 0) AS consecutivo
FROM public.radicados
WHERE numero_radicado LIKE 'E-' || EXTRACT(YEAR FROM CURRENT_DATE)::TEXT || '-%'
ON CONFLICT (anio, prefijo) 
DO UPDATE SET consecutivo = GREATEST(public.radicado_consecutivos.consecutivo, EXCLUDED.consecutivo);

INSERT INTO public.radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER AS anio,
    'S' AS prefijo,
    COALESCE(MAX(NULLIF(regexp_replace(numero_radicado, '^.*-', ''), '')::INTEGER), 0) AS consecutivo
FROM public.radicados
WHERE numero_radicado LIKE 'S-' || EXTRACT(YEAR FROM CURRENT_DATE)::TEXT || '-%'
ON CONFLICT (anio, prefijo) 
DO UPDATE SET consecutivo = GREATEST(public.radicado_consecutivos.consecutivo, EXCLUDED.consecutivo);

INSERT INTO public.radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER AS anio,
    'INT' AS prefijo,
    COALESCE(MAX(NULLIF(regexp_replace(numero_radicado, '^.*-', ''), '')::INTEGER), 0) AS consecutivo
FROM public.radicados
WHERE numero_radicado LIKE 'INT-' || EXTRACT(YEAR FROM CURRENT_DATE)::TEXT || '-%'
ON CONFLICT (anio, prefijo) 
DO UPDATE SET consecutivo = GREATEST(public.radicado_consecutivos.consecutivo, EXCLUDED.consecutivo);
