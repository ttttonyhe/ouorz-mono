const bcrypt = require('bcryptjs');
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();
const SALT_ROUNDS = 10;

const hashPassword = password => {
  return bcrypt.hashSync(password, SALT_ROUNDS);
};

async function main() {
  await prisma.account.upsert({
    where: { username: 'helipeng' },
    update: {},
    create: {
      username: 'helipeng',
      password: hashPassword(process.env.ADMIN_PASSWORD || 'ouorz-analytics'),
      is_admin: true,
    },
  });
}

main()
  .catch(e => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
