--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

-- Started on 2025-10-18 23:15:27

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 217 (class 1259 OID 112084)
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 112091)
-- Name: order; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public."order" (
    id integer NOT NULL,
    items json NOT NULL,
    pickup_time timestamp(0) without time zone NOT NULL,
    vip boolean NOT NULL,
    status character varying(20) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public."order" OWNER TO postgres;

--
-- TOC entry 4803 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN "order".pickup_time; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public."order".pickup_time IS '(DC2Type:datetime_immutable)';


--
-- TOC entry 4804 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN "order".created_at; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public."order".created_at IS '(DC2Type:datetime_immutable)';


--
-- TOC entry 218 (class 1259 OID 112090)
-- Name: order_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.order_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_id_seq OWNER TO postgres;

--
-- TOC entry 4795 (class 0 OID 112084)
-- Dependencies: 217
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20251018045118	2025-10-18 04:52:14	16
\.


--
-- TOC entry 4797 (class 0 OID 112091)
-- Dependencies: 219
-- Data for Name: order; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public."order" (id, items, pickup_time, vip, status, created_at) FROM stdin;
39	["Samosha","Idly"]	2025-10-17 10:30:00	t	active	2025-10-18 16:42:49
40	["Bread","Rice"]	2025-10-17 10:30:00	f	active	2025-10-18 16:43:13
38	["burger","fries"]	2025-10-18 12:30:00	t	completed	2025-10-18 16:42:19
\.


--
-- TOC entry 4805 (class 0 OID 0)
-- Dependencies: 218
-- Name: order_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_id_seq', 40, true);


--
-- TOC entry 4647 (class 2606 OID 112089)
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- TOC entry 4649 (class 2606 OID 112097)
-- Name: order order_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public."order"
    ADD CONSTRAINT order_pkey PRIMARY KEY (id);


-- Completed on 2025-10-18 23:15:27

--
-- PostgreSQL database dump complete
--

