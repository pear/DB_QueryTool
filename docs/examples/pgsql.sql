--
-- PostgreSQL database dump
--

\connect - postgres

SET search_path = public, pg_catalog;

--
-- TOC entry 4 (OID 16977)
-- Name: time; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "time" (
    id bigint DEFAULT '0' NOT NULL,
    user_id bigint DEFAULT '0' NOT NULL,
    projecttree_id bigint DEFAULT '0' NOT NULL,
    task_id bigint DEFAULT '0' NOT NULL,
    "timestamp" bigint DEFAULT '0' NOT NULL,
    durationsec bigint DEFAULT '0' NOT NULL,
    "comment" text NOT NULL
);


--
-- TOC entry 5 (OID 16991)
-- Name: uuser; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE uuser (
    id bigint DEFAULT '0' NOT NULL,
    login character varying(20) DEFAULT '' NOT NULL,
    name character varying(100) DEFAULT '' NOT NULL,
    surname character varying(100) DEFAULT '' NOT NULL,
    email character varying(100) DEFAULT '' NOT NULL,
    isadmin integer DEFAULT '0' NOT NULL,
    "password" character varying(32)
);


\connect - test

SET search_path = public, pg_catalog;

--
-- TOC entry 2 (OID 17002)
-- Name: uuser_seq; Type: SEQUENCE; Schema: public; Owner: test
--

CREATE SEQUENCE uuser_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


\connect - postgres

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 7 (OID 16977)
-- Name: time; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY "time" (id, user_id, projecttree_id, task_id, "timestamp", durationsec, "comment") FROM stdin;
1	3	23	123	2147483647	100	finishing the DB_QueryTool to be ready for PEAR
2	4	2	232	1112983792	200	quality control :-)
3	3	0	234	2147483647	21321	another entry, just to show the join thingy :-)
\.


--
-- Data for TOC entry 8 (OID 16991)
-- Name: uuser; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY uuser (id, login, name, surname, email, isadmin, "password") FROM stdin;
4	pp	Paolo	Panto	pp@visionp.de	1	\N
3	cain	Wolfram	Kriesing	wk@visionp.de	1	8a9d62c756bd894451e63e9a511ded0c
\.


--
-- TOC entry 6 (OID 17001)
-- Name: id_uuser_ukey; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX id_uuser_ukey ON uuser USING btree (id);


\connect - test

SET search_path = public, pg_catalog;

--
-- TOC entry 3 (OID 17002)
-- Name: uuser_seq; Type: SEQUENCE SET; Schema: public; Owner: test
--

SELECT pg_catalog.setval ('uuser_seq', 5, true);


