import Fastify from "fastify";
import dotenv from "dotenv";
import cors from "@fastify/cors";

dotenv.config();

const fastify = Fastify({ logger: true });

await fastify.register(cors, { origin: true });

fastify.get("/health", async () => ({
  status: "ok",
  service: "fingerprintweb-backend",
  ts: Date.now(),
}));

const port = Number(process.env.PORT) || 4000;

fastify
  .listen({ port, host: "0.0.0.0" })
  .then(() => console.log(`API listening on :${port}`))
  .catch((err: unknown) => {
    fastify.log.error(err);
    process.exit(1);
  });
